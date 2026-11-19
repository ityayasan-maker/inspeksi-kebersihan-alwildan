/**
 * upload.js — foto upload, preview, gallery
 */

const getBaseUrl = () => window.BASE_URL || '';

function compressCanvas(file, maxDim, quality, cb) {
  if (!file || !file.type.startsWith('image/')) { cb(file, null); return; }
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = new Image();
    img.onload = function() {
      let w = img.width, h = img.height;
      if (w > maxDim || h > maxDim) {
        if (w > h) { h = Math.round((h * maxDim) / w); w = maxDim; }
        else { w = Math.round((w * maxDim) / h); h = maxDim; }
      }
      const canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, w, h);
      canvas.toBlob(function(blob) {
        if (!blob) { cb(file, e.target.result); return; }
        try {
          const compFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg', lastModified: Date.now() });
          cb(compFile, canvas.toDataURL('image/jpeg', quality));
        } catch(err) {
          cb(file, e.target.result);
        }
      }, 'image/jpeg', quality);
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
}

function handleFotoChange(input) {
  const uid = input.dataset.uid;
  const file = input.files[0];
  if (!file) return;

  compressCanvas(file, 1280, 0.8, function(compFile, dataUrl) {
    if (window.DataTransfer && compFile !== file) {
      try {
        const dt = new DataTransfer();
        dt.items.add(compFile);
        input.files = dt.files;
      } catch(e) {}
    }

    Swal.fire({
      title: 'Upload foto ini?',
      html: `<img src="${dataUrl || ''}" class="img-fluid rounded mb-2 shadow-xs" style="max-height:220px; object-fit:contain">
             <div class="small text-muted">${escH(file.name)} (${Math.round(compFile.size/1024)} KB)</div>`,
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-cloud-arrow-up me-1"></i>Upload',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#166534',
    }).then(r => {
      if (r.isConfirmed) doUpload(uid);
      else input.value = '';
    });
  });
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

  fetch(getBaseUrl()+'/upload/inspeksi-foto', {
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
    fetch(getBaseUrl()+'/upload/delete',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd})
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

function openGallery(url, caption, badgeText = '', badgeClass = 'bg-success') {
  const modalEl = document.getElementById('galleryModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  document.getElementById('galleryGrid').classList.add('d-none');
  document.getElementById('galleryLoading').classList.add('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');
  document.getElementById('galleryMain').classList.remove('d-none');
  document.getElementById('galleryMainImg').src = url;
  document.getElementById('galleryCaption').textContent = caption || '';

  const badgeEl = document.getElementById('galleryMainBadge');
  if (badgeEl) {
    if (badgeText) {
      badgeEl.textContent = badgeText;
      badgeEl.className = 'badge ' + badgeClass;
      badgeEl.classList.remove('d-none');
    } else {
      badgeEl.classList.add('d-none');
    }
  }

  document.getElementById('galleryTitle').textContent = 'Detail Foto Dokumentasi';
  modal.show();
}

function backToGrid() {
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGrid').classList.remove('d-none');
  document.getElementById('galleryTitle').textContent = window._currentGalleryTitle || 'Galeri Dokumentasi Foto';
}

function loadGallery(inspeksiId) {
  const modalEl = document.getElementById('galleryModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  window._currentGalleryTitle = 'Dokumentasi Foto Inspeksi #' + inspeksiId;
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGrid').innerHTML = '';
  document.getElementById('galleryGrid').classList.remove('d-none');
  document.getElementById('galleryLoading').classList.remove('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');
  document.getElementById('galleryTitle').textContent = window._currentGalleryTitle;
  modal.show();

  const url = `${getBaseUrl()}/upload/gallery/${inspeksiId}`;
  fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(data => {
      document.getElementById('galleryLoading').classList.add('d-none');
      if (!data.photos || !data.photos.length) {
        document.getElementById('galleryEmpty').classList.remove('d-none');
        return;
      }

      const grid = document.getElementById('galleryGrid');
      const kc = k => k === 'bersih' ? 'bg-success' : (k === 'kurang_bersih' ? 'bg-warning text-dark' : 'bg-danger');
      const kl = k => k === 'bersih' ? 'Bersih' : (k === 'kurang_bersih' ? 'Kurang' : 'Kotor');

      data.photos.forEach(p => {
        const isRuangan = p.is_ruangan || false;
        const badgeClass = isRuangan ? 'bg-primary' : kc(p.kondisi);
        const badgeText = isRuangan ? 'Ruangan' : kl(p.kondisi);

        grid.innerHTML += `
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100 bg-secondary bg-opacity-25 border border-secondary border-opacity-50 overflow-hidden shadow-sm"
               style="cursor:pointer; border-radius:10px;"
               onclick="openGallery('${p.url}', '${escH(p.aspek)}', '${badgeText}', '${badgeClass}')">
            <div class="position-relative" style="aspect-ratio:4/3; background:#111">
              <img src="${p.url}" class="w-100 h-100" style="object-fit:cover" loading="lazy" alt="${escH(p.aspek)}">
              <div class="position-absolute top-0 end-0 m-1">
                <span class="badge ${badgeClass}" style="font-size:10px">${badgeText}</span>
              </div>
            </div>
            <div class="p-2" style="background:rgba(20,25,35,0.85)">
              <div class="text-white small fw-semibold text-truncate" title="${escH(p.aspek)}" style="font-size:11.5px">
                ${escH(p.aspek)}
              </div>
              <div class="text-white-50" style="font-size:10px">
                <i class="bi bi-arrows-fullscreen me-1"></i>Klik perbesar
              </div>
            </div>
          </div>
        </div>`;
      });
    })
    .catch(err => {
      console.warn('[Gallery] Fetch error:', err);
      document.getElementById('galleryLoading').classList.add('d-none');
      document.getElementById('galleryEmpty').classList.remove('d-none');
    });
}

function showToast(type, message) {
  Swal.fire({toast:true,position:'top-end',icon:type,title:message,
    showConfirmButton:false,timer:2500,timerProgressBar:true});
}

function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
