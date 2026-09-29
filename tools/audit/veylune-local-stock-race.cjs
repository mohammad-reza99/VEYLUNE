// Local DDEV only. Requires an explicit Sand=1 reset before execution.
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const crypto=require('node:crypto');
const fs=require('node:fs');
const path=require('node:path');
const base='https://veylune-shopware.ddev.site/__commerce-test';
const product=crypto.createHash('md5').update('veylune-local-variant-sand-product').digest('hex');
const output=process.env.VEYLUNE_TEST_OUTPUT||path.resolve('var/local-stock-race');
(async()=>{
 fs.mkdirSync(output,{recursive:true});
 const browser=await chromium.launch({headless:true,executablePath:process.env.VEYLUNE_BROWSER_EXECUTABLE||undefined});
 const contexts=[];
 try{
  async function prepare(index){
   const ctx=await browser.newContext({ignoreHTTPSErrors:true,viewport:{width:1280,height:900}}); contexts.push(ctx);
   const page=await ctx.newPage();
   await page.goto(base+'/detail/'+product,{waitUntil:'networkidle'});
   assert.equal(await page.locator('.btn-buy:enabled').count(),1,'Sand must start with one available unit');
   await page.locator('.btn-buy').click();
   await page.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
   await page.goto(base+'/checkout/register',{waitUntil:'networkidle'});
   const unique=Date.now()+'-'+index+'-'+crypto.randomBytes(3).toString('hex');
   const fields={'billingAddress[firstName]':'Race','billingAddress[lastName]':'QA '+index,'email':'race-'+unique+'@example.invalid','billingAddress[street]':'Teststrasse '+index,'billingAddress[zipcode]':'10115','billingAddress[city]':'Berlin'};
   for(const [name,value] of Object.entries(fields)) await page.locator('[name="'+name+'"]').fill(value);
   await page.locator('form[action$="/account/register"] button[type=submit]').click();
   await page.waitForURL('**/checkout/confirm');
   await page.locator('#tos').check();
   return page;
  }
  const [a,b]=await Promise.all([prepare(1),prepare(2)]);
  await Promise.all([a.locator('#confirmFormSubmit').click(),b.locator('#confirmFormSubmit').click()]);
  await Promise.all([a.waitForLoadState('networkidle').catch(()=>{}),b.waitForLoadState('networkidle').catch(()=>{})]);
  await a.waitForTimeout(1200);
  const pages=[a,b];
  const urls=pages.map(page=>page.url());
  const success=urls.filter(url=>url.includes('/checkout/finish')).length;
  assert.equal(success,1,'exactly one concurrent checkout may consume the final unit');
  const rejected=pages[urls.findIndex(url=>!url.includes('/checkout/finish'))];
  assert.ok(await rejected.locator('.alert-danger, .flashbags .alert, .cart-item, .checkout').count()>0,'losing checkout remains on a controlled storefront surface');
  const result={status:'pass',checks:['two independent carts reached confirm with the same final unit','exactly one concurrent order reached finish','losing checkout stayed on a controlled storefront surface'],successCount:success};
  fs.writeFileSync(path.join(output,'stock-race.json'),JSON.stringify(result,null,2));
  console.log(JSON.stringify(result,null,2));
 }catch(error){fs.writeFileSync(path.join(output,'stock-race.json'),JSON.stringify({status:'fail',error:error.message},null,2));throw error;}
 finally{for(const ctx of contexts) await ctx.close();await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1});
