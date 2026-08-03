<template>
  <div>
    <h2>Product Editor</h2>
    <label>Product UUID: <input v-model="productUuid" placeholder="Enter product UUID"/></label>
    <div id="dropzone" @click="choose" @dragover.prevent @drop.prevent="onDrop" style="border:2px dashed #888;padding:16px;margin-top:8px;">
      Drop images or click to select
      <input ref="file" type="file" multiple accept="image/*" @change="onFiles" style="display:none" />
    </div>
    <div style="display:flex;gap:12px;margin-top:12px;flex-wrap:wrap">
      <img v-for="(p,i) in previews" :key="i" :src="p" style="width:120px;object-fit:cover" />
    </div>
    <button @click="upload">Upload Selected</button>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return { productUuid:'', files:[], previews:[] }
  },
  methods: {
    choose() { this.$refs.file.click() },
    onFiles(e) { this.addFiles(e.target.files) },
    onDrop(e) { const f = Array.from(e.dataTransfer.files); this.addFiles(f) },
    addFiles(files) {
      files.forEach(f=>{
        if(!f.type.startsWith('image/')) return;
        this.files.push(f);
        const r = new FileReader(); r.onload = ev => this.previews.push(ev.target.result); r.readAsDataURL(f);
      })
    },
    async upload() {
      if(!this.productUuid) return alert('Enter product UUID');
      if(this.files.length === 0) return alert('Select files');
      const form = new FormData(); form.append('product_uuid', this.productUuid);
      for(const f of this.files) form.append('images[]', f, f.name);
      try {
        const { data } = await axios.post('/api/images/upload', form, { headers:{ 'Content-Type':'multipart/form-data' } });
        if(data.success) { alert('Uploaded ' + (data.uploaded?.length||0)); this.files=[]; this.previews=[] }
        else alert('Upload failed: ' + JSON.stringify(data));
      } catch(err) { alert('Upload error: ' + err.message) }
    }
  }
}
</script>
