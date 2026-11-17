/**
 * upload.js — foto upload, preview, gallery
 */

function handleFotoChange(input) {
  const uid = input.dataset.uid;
  const file = input.files[0];
  if (!file) return;
  const allowed = ['image/jpeg','image/png','image/webp'];
  if (!allowed.includes(file.type)) {
    Swal.fire({icon:'error',title:'Format tidak didukung',text:'Gunakan JPG, PNG, atau WebP.'});
    input.value=''; return;
  }
  if (file.size/1024 > 2048) {
    Swal.fire({icon:'error',title:'File terlalu besar',text:'Maksimal 2 MB.'});
    input.value=''; return;
  }
  const reader = new FileReader();
  reader.onload = e => {
    Swal.fire({
      title:'Upload foto ini?',
      html:`<img src="${e.target.result}" class="img-fluid rounded mb-2" style="max-height:200px">
            <div class="small text-muted">${escH(file.name)}</div>`,
      showCancelButton:true, confirmButtonText:'Upload', cancelButtonText:'Batal',
      confirmButtonColor:'#2d8653',
    }).then(r => { if(r.isConfirmed) doUpload(uid); else input.value=''; });
  };
  reader.readAsDataURL(file);
}

function doUpload(uid) {
  const input      = document.getElementById(uid+'_file');
  const inspeksiId = input.dataset.inspeksiId;
  const standarId  = input.dataset.standarId;
  const file       = input.files[0];
  if (!file) return;

  document.getElementById(uid+'_progress')?.classList.remove('d-none');
  document.getElementById(uid+'_input')?.classList.add('d-none');

  const fd = new FormData();
  fd.append('foto', file);
  fd.append('inspeksi_id', inspeksiId);
  fd.append('standar_id',  standarId);

  fetch(BASE_URL+'/upload/inspeksi-foto', {
    method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd
  })
  .then(r=>r.json())
  .then(data => {
    document.getElementById(uid+'_progress')?.classList.add('d-none');
    document.getElementById(uid+'_input')?.classList.remove('d-none');
    if (data.success) {
      renderPreview(uid, data.data.url, data.data.filename, inspeksiId, standarId);
      showToast('success','Foto berhasil diupload');
    } else {
      Swal.fire({icon:'error',title:'Upload gagal',text:data.message||'Coba lagi.'});
    }
  })
  .catch(()=>{
    document.getElementById(uid+'_progress')?.classList.add('d-none');
    document.getElementById(uid+'_input')?.classList.remove('d-none');
    Swal.fire({icon:'error',title:'Koneksi gagal',text:'Periksa koneksi internet.'});
  });
}

function renderPreview(uid, url, filename, inspeksiId, standarId) {
  document.getElementById(uid+'_preview')?.remove();
  const wrap = document.getElementById(uid);
  if (!wrap) return;
  const el = document.createElement('div');
  el.id = uid+'_preview';
  el.className = 'd-flex align-items-center gap-2 mb-2';
  el.innerHTML = `
    <img src="${url}" class="rounded border" style="width:60px;height:60px;object-fit:cover;cursor:pointer"
         onclick="openGallery('${url}','${escH(filename)}')">
    <div>
      <div class="small text-muted" style="font-size:11px">${escH(filename)}</div>
      <button type="button" class="btn btn-sm btn-outline-danger mt-1" style="font-size:11px;padding:2px 8px"
        onclick="deleteFoto('${uid}',${inspeksiId},${standarId},'${filename}')">
        <i class="bi bi-trash"></i> Hapus
      </button>
    </div>`;
  wrap.insertBefore(el, document.getElementById(uid+'_input'));
  const lbl = document.getElementById(uid+'_label');
  if(lbl) lbl.textContent='Ganti Foto';
}

function deleteFoto(uid, inspeksiId, standarId, filename) {
  Swal.fire({
    title:'Hapus foto ini?',icon:'warning',showCancelButton:true,
    confirmButtonColor:'#dc3545',confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',
  }).then(r => {
    if (!r.isConfirmed) return;
    const fd = new FormData();
    fd.append('_csrf', window.CSRF_TOKEN||'');
    fd.append('inspeksi_id', inspeksiId);
    fd.append('standar_id',  standarId);
    fd.append('filename',    filename);
    fetch(BASE_URL+'/upload/delete',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd})
    .then(r=>r.json()).then(data=>{
      if(data.success){
        document.getElementById(uid+'_preview')?.remove();
        const lbl=document.getElementById(uid+'_label');
        if(lbl) lbl.textContent='Tambah Foto';
        showToast('success','Foto dihapus');
      } else { Swal.fire({icon:'error',title:'Gagal',text:data.message}); }
    });
  });
}

function openGallery(url, caption) {
  const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
  document.getElementById('galleryGrid').innerHTML='';
  document.getElementById('galleryLoading').classList.add('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');
  document.getElementById('galleryMain').classList.remove('d-none');
  document.getElementById('galleryMainImg').src = url;
  document.getElementById('galleryCaption').textContent = caption||'';
  document.getElementById('galleryTitle').textContent = 'Foto';
  modal.show();
}

function loadGallery(inspeksiId) {
  const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGrid').innerHTML='';
  document.getElementById('galleryLoading').classList.remove('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');
  document.getElementById('galleryTitle').textContent='Galeri #'+inspeksiId;
  modal.show();

  fetch(`${BASE_URL}/upload/gallery/${inspeksiId}`,{headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(r=>r.json())
  .then(data=>{
    document.getElementById('galleryLoading').classList.add('d-none');
    if(!data.photos||!data.photos.length){
      document.getElementById('galleryEmpty').classList.remove('d-none'); return;
    }
    const grid=document.getElementById('galleryGrid');
    const kc = k => k==='bersih'?'success':k==='kurang_bersih'?'warning':'danger';
    data.photos.forEach(p=>{
      grid.innerHTML+=`<div class="col-6 col-md-4 col-lg-3">
        <div class="position-relative rounded overflow-hidden border border-secondary"
             style="aspect-ratio:1;cursor:pointer"
             onclick="openGallery('${p.url}','${escH(p.aspek)}')">
          <img src="${p.url}" class="w-100 h-100" style="object-fit:cover">
          <div class="position-absolute bottom-0 start-0 end-0 p-1" style="background:rgba(0,0,0,.6)">
            <div class="text-white" style="font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escH(p.aspek)}</div>
            <span class="badge bg-${kc(p.kondisi)}" style="font-size:9px">${p.kondisi.replace('_',' ')}</span>
          </div>
        </div>
      </div>`;
    });
  })
  .catch(()=>{
    document.getElementById('galleryLoading').classList.add('d-none');
    document.getElementById('galleryEmpty').classList.remove('d-none');
  });
}

function showToast(type, message) {
  Swal.fire({toast:true,position:'top-end',icon:type,title:message,
    showConfirmButton:false,timer:2500,timerProgressBar:true});
}

function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
