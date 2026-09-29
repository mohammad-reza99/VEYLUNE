// Local DDEV only. Creates a test customer. Order creation requires explicit opt-in.
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const crypto=require('node:crypto');
const base='https://veylune-shopware.ddev.site/__commerce-test';
const output=process.env.VEYLUNE_TEST_OUTPUT || path.resolve('var/local-account-smoke');
(async()=>{
 fs.mkdirSync(output,{recursive:true});
 const browser=await chromium.launch({headless:true,executablePath:process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined});
 const checks=[]; const errors=[];
 try{
  const ctx=await browser.newContext({ignoreHTTPSErrors:true,viewport:{width:1440,height:1000}});
  const page=await ctx.newPage();
  page.on('pageerror',e=>errors.push(e.message));
  const go=async route=>{const r=await page.goto(base+route,{waitUntil:'networkidle'});assert.equal(r.status(),200,route);};
  await go('/account/register');
  const consent=page.getByRole('button',{name:'Only technically required',exact:true});
  if(await consent.isVisible()) await consent.click();
  const email='account-qa-'+Date.now()+'@example.invalid';
  const password=crypto.randomBytes(18).toString('base64url')+'Aa1!';
  const form=page.locator('form[action$="/account/register"]');
  const fields={'billingAddress[firstName]':'Veylune','billingAddress[lastName]':'Account QA','email':email,'password':password,'billingAddress[street]':'Teststrasse 1','billingAddress[zipcode]':'10115','billingAddress[city]':'Berlin'};
  for(const [name,value] of Object.entries(fields)) await form.locator('[name="'+name+'"]').fill(value);
  await form.locator('button[type=submit]').click();
  await page.waitForURL('**/account',{timeout:20000});
  await page.waitForLoadState('networkidle');
  assert.match(await page.locator('.account-content-main').innerText(),/Veylune/);
  checks.push('registered customer creation and authenticated overview');
  for(const route of ['/account','/account/profile','/account/address','/account/order']){
   await go(route);
   for(const width of [390,1440]){
    await page.setViewportSize({width,height:1000});
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,route+' overflow '+width);
    await page.screenshot({path:path.join(output,route.replaceAll('/','-')+'-'+width+'.png'),fullPage:true});
   }
  }
  checks.push('overview/profile/address/order routes: desktop and mobile no overflow');
  assert.equal(await page.locator('.order-item').count(),0);
  checks.push('new customer has empty order history');
  await go('/account/logout');
  await go('/account');
  assert.ok(await page.locator('form[action$="/account/login"]').count()>0);
  checks.push('logout protects account');
  let login=page.locator('form[action$="/account/login"]');
  await login.locator('[name=username]').fill(email);
  await login.locator('[name=password]').fill('Wrong-password-123!');
  await login.locator('button[type=submit]').click();
  await page.waitForLoadState('networkidle');
  assert.ok(await page.locator('.alert-danger, .is-invalid').count()>0);
  checks.push('wrong password rejected');
  login=page.locator('form[action$="/account/login"]');
  await login.locator('[name=username]').fill(email);
  await login.locator('[name=password]').fill(password);
  await login.locator('button[type=submit]').click();
  await page.waitForURL('**/account');
  checks.push('valid login restores account');
  if(process.env.VEYLUNE_ALLOW_TEST_ORDER==='1'){
   await go('/detail/63f8706983fe4eb8b7c8806060e378ba');
   await page.locator('.btn-buy').first().click();
   await page.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
   await go('/checkout/confirm');
   assert.ok(await page.locator('#paymentMethod019f7a00000070008000000000000003').isChecked(),'only local success simulator selected');
   await page.locator('#tos').check();
   await page.locator('#confirmFormSubmit').click();
   await page.waitForURL('**/checkout/finish?**',{timeout:20000});
   const orderId=new URL(page.url()).searchParams.get('orderId');
   assert.match(orderId,/^[a-f0-9]{32}$/);
   await go('/account/order');
   await page.getByRole('button',{name:/show details/i}).first().click();
   await page.waitForTimeout(700);
   assert.match(await page.locator('.account-content-main').innerText(),/Aurelia|VLT-TEST-F01/);
   await page.screenshot({path:path.join(output,'order-history-populated.png'),fullPage:true});
   await page.setViewportSize({width:390,height:1000});
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'populated history mobile overflow');
   await page.screenshot({path:path.join(output,'order-history-populated-mobile.png'),fullPage:true});
   checks.push('registered checkout creates local order visible in own history');
   const other=await browser.newContext({ignoreHTTPSErrors:true});
   const outsider=await other.newPage();
   await outsider.goto(base+'/account/order',{waitUntil:'networkidle'});
   assert.ok(await outsider.locator('form[action$="/account/login"]').count()>0,'anonymous history requires login');
   assert.equal(await outsider.locator('.account-content-main').getByText('VLT-TEST-F01',{exact:false}).count(),0);
   await other.close();
   checks.push('anonymous visitor cannot view registered order history');
   await page.setViewportSize({width:1440,height:1000});
   await go('/account/order');
   await page.locator('.order-table-header-context-menu[data-bs-toggle="dropdown"]').first().click();
   await page.locator('form[id^="orderDetailForm-"] button[type=submit]').first().click();
   await page.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
   await go('/checkout/cart');
   assert.match(await page.locator('.checkout').innerText(),/VLT-TEST-F01/);
   checks.push('repeat order restores original product to native cart');
   await go('/account/order');
   await page.locator('.order-table-header-context-menu[data-bs-toggle="dropdown"]').first().click();
   await page.locator('[data-bs-target^="#cancelOrderModal-"]').first().click();
   await page.locator('.modal.show').waitFor({state:'visible'});
   await page.locator('.modal.show form[action$="/account/order/cancel"] button[type=submit]').click();
   await page.waitForLoadState('networkidle');
   await go('/account/order');
   assert.match(await page.locator('.account-content-main').innerText(),/cancelled|canceled/i);
   checks.push('customer cancellation updates order status; not a money refund');
  }
  assert.deepEqual(errors,[]);
  fs.writeFileSync(path.join(output,'account-result.json'),JSON.stringify({status:'pass',checks},null,2));
  console.log(JSON.stringify({status:'pass',checks},null,2));
 }catch(error){
  fs.writeFileSync(path.join(output,'account-result.json'),JSON.stringify({status:'fail',checks,error:error.message},null,2));
  throw error;
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
