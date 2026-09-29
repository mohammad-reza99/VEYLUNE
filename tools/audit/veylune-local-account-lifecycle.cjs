// Local-only fixtures: creates two customers and one simulated order.
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs'), path=require('node:path'), crypto=require('node:crypto');
const base='https://veylune-shopware.ddev.site/__commerce-test';
const out=process.env.VEYLUNE_TEST_OUTPUT || path.resolve('var/account-lifecycle');
const checks=[];
const password=()=>crypto.randomBytes(20).toString('base64url')+'Aa1!';
(async()=>{
 assert.equal(process.env.VEYLUNE_ALLOW_TEST_ORDER,'1','Set VEYLUNE_ALLOW_TEST_ORDER=1 to create local fixtures.');
 fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({headless:true,executablePath:process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined});
 try{
  const make=async suffix=>{
   const ctx=await browser.newContext({ignoreHTTPSErrors:true});
   const page=await ctx.newPage(), email='lifecycle-'+Date.now()+'-'+suffix+'@example.invalid', pass=password();
   await page.goto(base+'/account/register',{waitUntil:'networkidle'});
   const consent=page.getByRole('button',{name:'Only technically required',exact:true}); if(await consent.isVisible()) await consent.click();
   const form=page.locator('form[action$="/account/register"]');
   const data={'billingAddress[firstName]':'Lifecycle','billingAddress[lastName]':suffix,'email':email,'password':pass,'billingAddress[street]':'Teststrasse 1','billingAddress[zipcode]':'10115','billingAddress[city]':'Berlin'};
   for(const [name,value] of Object.entries(data)) await form.locator('[name="'+name+'"]').fill(value);
   await form.locator('button[type=submit]').click(); await page.waitForURL('**/account');
   return {ctx,page,email,pass};
  };
  const a=await make('Owner'), p=a.page;
  const go=async route=>p.goto(base+route,{waitUntil:'networkidle'});
  await go('/account/profile');
  const profile=p.locator('#profilePersonalForm');
  await profile.locator('[name=firstName]').fill('UpdatedQA');
  await profile.locator('button[type=submit]').click(); await p.waitForLoadState('networkidle');
  await go('/account/profile'); assert.equal(await p.locator('#profilePersonalForm [name=firstName]').inputValue(),'UpdatedQA');
  checks.push('profile changes persist'); console.log(checks.at(-1));
  await go('/account/address');
  const addressUrl=await p.locator('a[href*="/account/address/"]').evaluateAll(es=>es.map(e=>e.href).find(h=>/\/account\/address\/[a-f0-9]{32}$/.test(h)));
  assert.ok(addressUrl);
  await p.goto(addressUrl,{waitUntil:'networkidle'});
  const address=p.locator('form[action*="/account/address/"]').filter({has:p.locator('[name="address[street]"]')});
  await address.locator('[name="address[street]"]').fill('Updated Teststrasse 42');
  await address.locator('button[type=submit]').click(); await p.waitForLoadState('networkidle');
  await p.goto(addressUrl,{waitUntil:'networkidle'});
  assert.equal(await p.locator('[name="address[street]"]').inputValue(),'Updated Teststrasse 42');
  checks.push('address changes persist'); console.log(checks.at(-1));
  await go('/detail/63f8706983fe4eb8b7c8806060e378ba');
  await p.locator('.btn-buy').first().click(); await p.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
  await go('/checkout/confirm');
  assert.ok(await p.locator('#paymentMethod019f7a00000070008000000000000003').isChecked());
  await p.locator('#tos').check(); await p.locator('#confirmFormSubmit').click();
  await p.waitForURL('**/checkout/finish?**');
  const orderId=new URL(p.url()).searchParams.get('orderId'); assert.match(orderId,/^[a-f0-9]{32}$/);
  const b=await make('Other');
  for(const url of [addressUrl,base+'/account/order/edit/'+orderId]){
   await b.page.goto(url,{waitUntil:'networkidle'});
   assert.equal(await b.page.locator('[name="address[street]"]').count(),0,'no foreign address editor');
   assert.doesNotMatch(await b.page.locator('body').innerText(),/Updated Teststrasse 42|VLT-TEST-F01/,'foreign data hidden');
   assert.equal(await b.page.locator('#confirmOrderForm').count(),0,'no foreign order editor');
  }
  await b.page.goto(base+'/account/order',{waitUntil:'networkidle'});
  assert.doesNotMatch(await b.page.locator('.account-content-main').innerText(),/VLT-TEST-F01|Aurelia/);
  checks.push('two registered accounts cannot read each other address or order details'); console.log(checks.at(-1));
  await go('/account/logout'); await go('/account/recover');
  await p.locator('[name="email[email]"]').fill(a.email);
  await p.locator('.account-recover-password-submit').click(); await p.waitForLoadState('networkidle');
  const mailBase='https://veylune-shopware.ddev.site:8026';
  let message;
  for(let i=0;i<15;i++){
   const r=await a.ctx.request.get(mailBase+'/api/v1/messages');
   assert.ok(r.ok(),'Mailpit reachable'); const data=await r.json();
   const item=(data.messages||[]).find(m=>(m.To||[]).some(t=>t.Address===a.email)&&/password|reset|recover/i.test(m.Subject));
   if(item){message=await (await a.ctx.request.get(mailBase+'/api/v1/message/'+item.ID)).json();break;}
   await p.waitForTimeout(1000);
  }
  assert.ok(message,'recovery mail captured locally');
  const content=message.HTML || message.Text || '';
  const match=content.match(/https?:[^\s"'<>]+\/account\/recover\/password\?[^\s"'<>]+/);
  assert.ok(match,'reset link in test mail');
  const reset=match[0].replaceAll('&amp;','&');
  assert.ok(reset.startsWith(base+'/account/recover/password?'),'reset link stays in local channel');
  await p.goto(reset,{waitUntil:'networkidle'});
  const next=password();
  await p.locator('[name="password[newPassword]"]').fill(next);
  await p.locator('[name="password[newPasswordConfirm]"]').fill(next);
  await p.locator('form[action*="/recover/password"] button[type=submit]').click(); await p.waitForLoadState('networkidle');
  await go('/account/logout'); await go('/account/login');
  let login=p.locator('form[action$="/account/login"]');
  await login.locator('[name=username]').fill(a.email); await login.locator('[name=password]').fill(a.pass);
  await login.locator('button[type=submit]').click(); await p.waitForLoadState('networkidle');
  assert.ok(await p.locator('.alert-danger, .is-invalid').count()>0,'old password rejected');
  login=p.locator('form[action$="/account/login"]');
  await login.locator('[name=username]').fill(a.email); await login.locator('[name=password]').fill(next);
  await login.locator('button[type=submit]').click(); await p.waitForURL('**/account');
  await go('/account/logout'); await p.goto(reset,{waitUntil:'networkidle'});
  assert.equal(await p.locator('[name="password[newPassword]"]').count(),0,'reset link cannot be reused');
  checks.push('Mailpit password reset; old password rejected, new password accepted, reset token single-use');
  fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'pass',checks},null,2));
  console.log(JSON.stringify({status:'pass',checks},null,2));
 }catch(e){fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'fail',checks,error:e.message},null,2));throw e;}
 finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
