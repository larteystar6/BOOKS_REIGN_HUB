<template>
  <div>
    <h2>POS</h2>
    <label>Cashier UUID: <input v-model="cashier" /></label>
    <div>
      <input v-model="sku" placeholder="Enter product SKU or UUID" @keyup.enter="addItem" />
      <button @click="addItem">Add</button>
    </div>
    <table>
      <tr><th>SKU/Name</th><th>Qty</th><th>Price</th><th>Total</th></tr>
      <tr v-for="(it,i) in items" :key="i">
        <td>{{ it.name || it.product_uuid }}</td>
        <td><input v-model.number="it.quantity" @change="recalc" style="width:60px" /></td>
        <td>{{ it.unit_price }}</td>
        <td>{{ (it.quantity * it.unit_price).toFixed(2) }}</td>
      </tr>
    </table>
    <div>Subtotal: {{ subtotal.toFixed(2) }}</div>
    <button @click="createSale">Create Sale</button>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data(){ return { cashier:'', sku:'', items:[] } },
  computed: { subtotal(){ return this.items.reduce((s,i)=>s + (i.quantity * i.unit_price),0) } },
  methods:{
    async addItem(){
      if(!this.sku) return;
      // try fetch product by sku
      try{
        const r = await axios.get('/api/products'); // placeholder - implement product search API
        // naive: push mock item
        this.items.push({ product_uuid: this.sku, name: this.sku, quantity:1, unit_price: 100 });
        this.sku='';
      } catch(e){
        // fallback
        this.items.push({ product_uuid: this.sku, name: this.sku, quantity:1, unit_price: 100 });
        this.sku='';
      }
    },
    recalc(){},
    async createSale(){
      if(!this.cashier) return alert('Enter cashier uuid');
      if(this.items.length===0) return alert('No items');
      const payload = { invoice_no: 'INV-'+Date.now(), cashier_uuid: this.cashier, items: this.items.map(i=>({ product_uuid:i.product_uuid, quantity:i.quantity, unit_price:i.unit_price })) };
      try{
        const { data } = await axios.post('/api/sales', payload);
        if(data.success) { alert('Sale created: ' + data.uuid); this.items=[] }
        else alert('Sale error: ' + JSON.stringify(data));
      } catch(err){ alert('Sale error: ' + err.message) }
    }
  }
}
</script>
