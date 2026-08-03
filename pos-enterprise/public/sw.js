const CACHE = 'pos-enterprise-cache-v1';
self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(['/pos-enterprise/public/','/pos-enterprise/public/index.html','/pos-enterprise/public/pos.js','/pos-enterprise/public/product.html'])));
});
self.addEventListener('fetch', e => {
  if(e.request.method !== 'GET') return;
  e.respondWith(caches.match(e.request).then(resp => resp || fetch(e.request).catch(()=> caches.match('/pos-enterprise/public/index.html'))));
});
