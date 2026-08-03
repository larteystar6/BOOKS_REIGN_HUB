// IndexedDB sync stub for frontend

export async function openDb(){
  return new Promise((resolve,reject)=>{
    const r = indexedDB.open('pos_enterprise_offline_v1',1);
    r.onupgradeneeded = e => { const d = e.target.result; if(!d.objectStoreNames.contains('sales')) d.createObjectStore('sales',{keyPath:'id',autoIncrement:true}); };
    r.onsuccess = e => resolve(e.target.result);
    r.onerror = e => reject(e.target.error);
  });
}

export async function enqueueSale(rec){
  const db = await openDb();
  const tx = db.transaction('sales','readwrite');
  tx.objectStore('sales').add({ created_at: new Date().toISOString(), payload: rec, status:'pending' });
  return new Promise(r=>tx.oncomplete = ()=>r(true));
}

export async function syncSales(){
  const db = await openDb();
  const tx = db.transaction('sales','readwrite');
  const store = tx.objectStore('sales');
  const req = store.openCursor();
  req.onsuccess = async e => {
    const cursor = e.target.result;
    if(cursor){
      const rec = cursor.value;
      if(rec.status === 'pending'){
        try{
          await fetch('/api/sales',{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(rec.payload) });
          cursor.delete();
        }catch(err){ console.error('sync failed',err) }
      }
      cursor.continue();
    }
  }
}
