const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const path=require('node:path');
const fs=require('node:fs');
const output=process.env.VEYLUNE_TEST_OUTPUT || path.resolve('var/local-checkout-smoke');
const base='https://veylune-shopware.ddev.site/__commerce-test';
(async()=>{
 fs.mkdirSync(output,{recursive:true});
 const browser=await chromium.launch({headless:true,executablePath:process.env.VEYLUNE_BROWSER_EXECUTABLE || undefined});
 try {
  const ctx=await browser.newContext({ignoreHTTPSErrors:true});
  const page=await ctx.newPage();
  const errors=[]; page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base+'/detail/63f8706983fe4eb8b7c8806060e378ba',{waitUntil:'networkidle'});
  await page.locator('.btn-buy').first().click();
  await page.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
  await page.goto(base+'/checkout/register',{waitUntil:'networkidle'});
  const fields={'billingAddress[firstName]':'Veylune','billingAddress[lastName]':'QA','email': 'qa-'+Date.now()+'@example.invalid','billingAddress[street]':'Teststrasse 1','billingAddress[zipcode]':'10115','billingAddress[city]':'Berlin'};
  for(const [name,value] of Object.entries(fields)) await page.locator('[name="'+name+'"]').fill(value);
  await page.locator('form[action$="/account/register"] button[type=submit]').click();
  await page.waitForURL('**/checkout/confirm');
  await page.waitForLoadState('networkidle');
  assert.equal(await page.locator('#confirmFormSubmit').count(),1);
  const consent=page.getByRole('button',{name:'Only technically required',exact:true});
  if(await consent.isVisible()) await consent.click();
  for(const width of [390,768,1440]){
   await page.setViewportSize({width,height:1000});
   await page.screenshot({path:path.join(output,'checkout-'+width+'.png'),fullPage:true});
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'overflow '+width);
   if(width===1440){
    const main=await page.locator('.checkout-main').boundingBox(), aside=await page.locator('.checkout-aside').boundingBox();
    assert.ok(aside.x>main.x+main.width-1,'summary alongside checkout');
   }
  }
  await page.locator('#tos').uncheck();
  await page.locator('#confirmFormSubmit').click();
  await page.waitForLoadState('networkidle');
  console.log('TERMS URL',page.url());
  assert.equal(await page.locator('#confirmFormSubmit').count(),1,'checkout remains available after invalid terms');
  assert.ok(!page.url().includes('/checkout/finish'),'invalid terms never reaches order success');
  assert.ok(await page.locator('.is-invalid, .alert-danger').count()>0,'validation feedback is visible');
  assert.deepEqual(errors,[]);
  console.log('PASS checkout 390/768/1440: no overflow; desktop summary alongside; terms validation retained.');
  fs.writeFileSync(path.join(output,'checkout-result.json'),JSON.stringify({status:'pass',checks:['fresh guest registration','390/768/1440 no overflow','desktop two-column checkout','unchecked terms rejected'],validOrderSubmitted:false},null,2));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
