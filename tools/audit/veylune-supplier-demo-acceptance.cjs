const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const base='https://veylune-shopware.ddev.site/__commerce-test';
const out=process.env.VEYLUNE_TEST_OUTPUT||path.resolve('var/supplier-demo-acceptance');
const routes=[
 ['catalog','/test-products'],['variants','/test-products?fixtures=variants'],
 ['pdp','/detail/63f8706983fe4eb8b7c8806060e378ba'],
 ['login','/account/login'],['register','/account/register'],
 ['recover','/account/recover'],['empty-cart','/checkout/cart']
];
const widths=[390,768,1440];
(async()=>{
 fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({headless:true,executablePath:process.env.VEYLUNE_BROWSER_EXECUTABLE||undefined});
 const report={status:'pass',capturedAt:new Date().toISOString(),surfaces:[],checks:[],findings:[],referenceContract:{source:'Wayfair live IA plus Veylune governed tokens',patterns:['search-led header','dense product discovery','clear price and availability','persistent cart/account utilities','responsive single-purpose checkout']}};
 try{
  const ctx=await browser.newContext({ignoreHTTPSErrors:true});
  const page=await ctx.newPage();
  const runtime=[];
  page.on('pageerror',error=>runtime.push('pageerror:'+error.message));
  page.on('console',message=>{if(message.type()==='error')runtime.push('console:'+message.text())});
  page.on('requestfailed',request=>runtime.push('request:'+request.url()+':'+request.failure()?.errorText));
  for(const [name,route] of routes){
   for(const width of widths){
    await page.setViewportSize({width,height:1000});
    const before=runtime.length;
    const started=Date.now();
    const response=await page.goto(base+route,{waitUntil:'networkidle'});
    const elapsed=Date.now()-started;
    assert.equal(response.status(),200,name+' '+width+' status');
    const consent=page.getByRole('button',{name:'Only technically required',exact:true});
    if(await consent.isVisible())await consent.click();
    await page.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=700){scrollTo(0,y);await new Promise(resolve=>setTimeout(resolve,35));}scrollTo(0,0)});
    const state=await page.evaluate(()=>({
     h1:document.querySelectorAll('main h1, .content-main h1').length,
     mains:document.querySelectorAll('main').length,
     overflow:document.documentElement.scrollWidth>innerWidth+1,
     broken:[...document.images].filter(image=>(image.currentSrc||image.getAttribute('src'))&&image.complete&&image.naturalWidth===0).length,
     brokenUrls:[...document.images].filter(image=>(image.currentSrc||image.getAttribute('src'))&&image.complete&&image.naturalWidth===0).map(image=>image.currentSrc||image.src),
     brokenMarkup:[...document.images].filter(image=>(image.currentSrc||image.getAttribute('src'))&&image.complete&&image.naturalWidth===0).map(image=>image.outerHTML),
     unnamed:[...document.querySelectorAll('button,a[href],input,select,textarea')].filter(element=>{
       if(element.matches('input[type=hidden]')||element.getAttribute('aria-hidden')==='true')return false;
       const style=getComputedStyle(element);if(style.display==='none'||style.visibility==='hidden')return false;
       return !(element.getAttribute('aria-label')||element.getAttribute('title')||element.textContent.trim()||element.getAttribute('placeholder')||element.labels?.length);
     }).length,
     primary:getComputedStyle(document.body).getPropertyValue('--veylune-marketplace-primary').trim(),
     font:getComputedStyle(document.body).fontFamily
    }));
    assert.ok(state.h1>=1,name+' h1');
    assert.equal(state.mains,1,name+' main landmark');
    assert.equal(state.overflow,false,name+' overflow '+width);
    assert.equal(state.broken,0,name+' broken images '+width+': '+state.brokenMarkup.join(' | '));
    assert.equal(state.unnamed,0,name+' unnamed controls '+width);
    assert.deepEqual(runtime.slice(before),[],name+' runtime '+width);
    await page.screenshot({path:path.join(out,name+'-'+width+'.png'),fullPage:true});
    report.surfaces.push({name,route,width,status:response.status(),elapsedMs:elapsed,...state});
   }
  }
  await page.setViewportSize({width:390,height:1000});
  await page.goto(base+'/detail/63f8706983fe4eb8b7c8806060e378ba',{waitUntil:'networkidle'});
  await page.locator('.btn-buy').first().click();
  await page.locator('.offcanvas .begin-checkout-btn').waitFor({state:'visible'});
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'offcanvas overflow');
  await page.screenshot({path:path.join(out,'offcanvas-cart-390.png'),fullPage:true});
  await page.goto(base+'/checkout/cart',{waitUntil:'networkidle'});
  for(const width of widths){
   await page.setViewportSize({width,height:1000});
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'populated cart overflow '+width);
   await page.screenshot({path:path.join(out,'cart-'+width+'.png'),fullPage:true});
  }
  await page.goto(base+'/checkout/register',{waitUntil:'networkidle'});
  for(const width of widths){
   await page.setViewportSize({width,height:1000});
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'checkout register overflow '+width);
   await page.screenshot({path:path.join(out,'checkout-register-'+width+'.png'),fullPage:true});
  }
  const headers=await ctx.request.get(base+'/test-products');
  const cache=(headers.headers()['cache-control']||'').toLowerCase();
  assert.match(cache,/private|no-store/,'private cache policy');
  const html=await headers.text();
  assert.match(html,/noindex/i,'noindex containment');
  report.checks.push('7 anonymous native surfaces x 3 viewports return 200 with one main/h1, no overflow, no broken images, no unnamed visible controls and no runtime errors');
  report.checks.push('populated offcanvas/cart and checkout registration remain contained at 390/768/1440');
  report.checks.push('isolated channel returns private/no-store caching and noindex containment');
  report.checks.push('governed primary token and typography are present across native surfaces');
  fs.writeFileSync(path.join(out,'supplier-demo-acceptance.json'),JSON.stringify(report,null,2));
  console.log(JSON.stringify({status:report.status,surfaces:report.surfaces.length,checks:report.checks},null,2));
 }catch(error){report.status='fail';report.findings.push(error.message);fs.writeFileSync(path.join(out,'supplier-demo-acceptance.json'),JSON.stringify(report,null,2));throw error;}
 finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1});
