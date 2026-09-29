const {chromium}=require('playwright');
const assert=require('node:assert/strict'), crypto=require('node:crypto'),fs=require('node:fs'),path=require('node:path');
const base='https://veylune-shopware.ddev.site/__commerce-test';
const id=key=>crypto.createHash('md5').update('veylune-local-variant-'+key+'-product').digest('hex');
const out=process.env.VEYLUNE_TEST_OUTPUT||path.resolve('var/variant-smoke');
(async()=>{
 fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({headless:true,executablePath:process.env.VEYLUNE_BROWSER_EXECUTABLE||undefined});
 const checks=[];
 try{
  const ctx=await browser.newContext({ignoreHTTPSErrors:true}),page=await ctx.newPage();
  await page.goto(base+'/detail/'+id('sand'),{waitUntil:'networkidle'});
  assert.ok(await page.locator('.btn-buy:enabled').count()>0);
  console.log('CONFIGURATOR',await page.locator('.product-detail-configurator').innerText());
  assert.match(await page.locator('.product-detail-configurator').innerText(),/Sand/);
  assert.match(await page.locator('.product-detail-configurator').innerText(),/Charcoal/);
  await page.locator('.btn-buy').first().click();
  await page.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
  await page.goto(base+'/checkout/cart',{waitUntil:'networkidle'});
  assert.match(await page.locator('.checkout').innerText(),/Sand/);
  assert.equal(await page.locator('input[name=quantity]').inputValue(),'1');
  checks.push('native configurator and Sand SKU preserved in cart');
  await page.locator('input[name=quantity]').evaluate(el=>{el.value='99';el.closest('form').submit();});
  await page.waitForLoadState('networkidle');
  await page.goto(base+'/checkout/cart',{waitUntil:'networkidle'});
  const quantity=Number(await page.locator('input[name=quantity]').inputValue());
  assert.ok(quantity>=1&&quantity<=3,'server caps quantity at current fixture stock');
  checks.push('forged quantity 99 cannot exceed current fixture stock');
  await page.goto(base+'/detail/'+id('charcoal'),{waitUntil:'networkidle'});
  assert.equal(await page.locator('.btn-buy:enabled').count(),0);
  checks.push('zero-stock Charcoal variant cannot be purchased');
  await page.screenshot({path:path.join(out,'charcoal.png'),fullPage:true});
  fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'pass',checks},null,2));
  console.log(JSON.stringify({status:'pass',checks},null,2));
 }catch(e){fs.writeFileSync(path.join(out,'result.json'),JSON.stringify({status:'fail',checks,error:e.message},null,2));throw e;}
 finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
