/* pos.js - indexedDB queues and sync hook */
const DB_NAME = 'pos_enterprise_offline_v1';
let db;
function openDb(){
  return new Promise((resolve,reject)=>{
    const r = indexedDB.open(DB_NAME,1);
    r.onupgradeneeded = e => {
      const d = e.target.result;
      if(!d.objectStoreNames.contains('products')) d.createObjectStore('products',{keyPath:'uuid'});
      if(!d.objectStoreNames.contains('sales_queue')) d.createObjectStore('sales_queue',{autoIncrement:true});
      if(!d.objectStoreNames.contains('image_queue')) d.createObjectStore('image_queue',{autoIncrement:true});
      if(!d.objectStoreNames.contains('sync_meta')) d.createObjectStore('sync_meta',{keyPath:'key'});
    };
    r.onsuccess = e => { db = e.target.result; resolve(db); };
    r.onerror = e => reject(e.target.error);
  });
}
openDb();

async function enqueueImage(product_uuid, blob, filename){
  const tx = db.transaction('image_queue','readwrite');
  const store = tx.objectStore('image_queue');
  const rec = {created_at:new Date().toISOString(), product_uuid, filename, blob:blob, status:'pending'};
  store.add(rec);
  return new Promise((res,rej)=>tx.oncomplete = ()=>res(true));
}

async function syncImages(){
  return new Promise((resolve,reject)=>{
    const tx = db.transaction('image_queue','readwrite');
    const store = tx.objectStore('image_queue');
    const req = store.openCursor();
    req.onsuccess = async e => {
      const cursor = e.target.result;
      if(cursor){
        const rec = cursor.value;
        if(rec.status === 'pending') {
          // try upload
          const fd = new FormData();
          fd.append('product_uuid', rec.product_uuid);
          fd.append('images[]', rec.blob, rec.filename);
          try {
            const r = await fetch('/pos-enterprise/api/uploads.php', {method:'POST', body:fd});
            const j = await r.json();
            if(j.success) {
              cursor.delete(); appendLog('Image synced for ' + rec.filename);
            } else {
              appendLog('Image sync failed: ' + JSON.stringify(j));
            }
          } catch(err){
            appendLog('Image sync network error: ' + err.message);
          }
        }
        cursor.continue();
      } else {
        appendLog('Image queue processed');
        resolve(true);
      }
    };
    req.onerror = e => reject(e.target.error);
  });
}

function appendLog(m){ console.log(new Date().toISOString() + ' - ' + m); }

document.addEventListener('DOMContentLoaded', ()=>{
  document.getElementById('syncNow')?.addEventListener('click', async ()=>{
    await syncImages();
    alert('Sync attempt finished - check console for details');
  });
});
