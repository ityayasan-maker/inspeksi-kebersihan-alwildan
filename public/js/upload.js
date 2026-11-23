/**
 * upload.js — Foto upload, preview, dan modal gallery inspeksi
 */

let currentGalleryPhotos = [];
let currentGalleryIndex = 0;

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

  const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
  fetch(baseUrl + '/upload/inspeksi-foto', {
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

    const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
    fetch(baseUrl + '/upload/delete', {
      method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd
    })
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

/**
 * Buka Galeri Foto untuk satu sesi inspeksi
 */
function loadGallery(inspeksiId, ruanganNama = '') {
  const modalEl = document.getElementById('galleryModal');
  if (!modalEl) {
    console.warn('#galleryModal tidak ditemukan di halaman');
    return;
  }
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  // Reset UI
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGridWrap').classList.remove('d-none');
  document.getElementById('galleryBtnBack').classList.add('d-none');
  document.getElementById('galleryGrid').innerHTML = '';
  document.getElementById('galleryLoading').classList.remove('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');
  
  const titleText = 'Galeri Foto #' + inspeksiId + (ruanganNama ? ' &mdash; ' + escH(ruanganNama) : '');
  document.getElementById('galleryTitle').innerHTML = '<i class="bi bi-images me-2 text-info"></i>' + titleText;
  modal.show();

  const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
  fetch(baseUrl + '/upload/gallery/' + inspeksiId, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('galleryLoading').classList.add('d-none');
    currentGalleryPhotos = data.photos || [];

    if (!currentGalleryPhotos.length) {
      document.getElementById('galleryEmpty').classList.remove('d-none');
      return;
    }

    const grid = document.getElementById('galleryGrid');
    const badgeClass = k => k === 'bersih' ? 'success' : (k === 'kurang_bersih' ? 'warning text-dark' : 'danger');
    const statusLabel = k => k === 'bersih' ? 'Bersih' : (k === 'kurang_bersih' ? 'Kurang' : 'Kotor');

    currentGalleryPhotos.forEach((p, idx) => {
      const isUtamaBadge = p.is_utama ? '<span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size:9.5px"><i class="bi bi-camera me-1"></i>Ruangan</span>' : '';
      grid.innerHTML += `
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card bg-dark border border-secondary border-opacity-50 overflow-hidden shadow-sm h-100" style="cursor:pointer; transition:transform .15s" onclick="viewGalleryPhoto(${idx})" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="position-relative" style="aspect-ratio: 4/3; background:#111">
              ${isUtamaBadge}
              <img src="${p.url}" class="w-100 h-100" style="object-fit:cover" alt="${escH(p.aspek)}" onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'120\\' height=\\'90\\' fill=\\'%23555\\'><rect width=\\'120\\' height=\\'90\\' fill=\\'%23222\\'/><text x=\\'50%\\' y=\\'50%\\' fill=\\'%23888\\' text-anchor=\\'middle\\' font-size=\\'12\\'>Foto</text></svg>'">
            </div>
            <div class="p-2" style="background:rgba(20,25,35,0.95)">
              <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                <span class="badge bg-${badgeClass(p.kondisi)}" style="font-size:9.5px">${statusLabel(p.kondisi)}</span>
                ${p.is_utama ? '<span class="text-white-50" style="font-size:9.5px">Utama</span>' : ''}
              </div>
              <div class="text-white text-truncate fw-semibold" style="font-size:11.5px" title="${escH(p.aspek)}">${escH(p.aspek)}</div>
              ${p.catatan ? `<div class="text-white-50 text-truncate mt-1" style="font-size:10.5px"><i class="bi bi-chat-text me-1"></i>${escH(p.catatan)}</div>` : ''}
            </div>
          </div>
        </div>`;
    });
  })
  .catch(err => {
    console.error('Gallery load error:', err);
    document.getElementById('galleryLoading').classList.add('d-none');
    document.getElementById('galleryEmpty').classList.remove('d-none');
  });
}

/**
 * Tampilkan foto tunggal (lightbox view)
 */
function viewGalleryPhoto(idx) {
  if (!currentGalleryPhotos || !currentGalleryPhotos[idx]) return;
  currentGalleryIndex = idx;
  const p = currentGalleryPhotos[idx];

  document.getElementById('galleryGridWrap').classList.add('d-none');
  document.getElementById('galleryMain').classList.remove('d-none');
  document.getElementById('galleryBtnBack').classList.remove('d-none');

  document.getElementById('galleryMainImg').src = p.url;
  document.getElementById('galleryCaption').textContent = p.aspek || 'Foto Dokumentasi';
  document.getElementById('gallerySubCaption').innerHTML = 
    (p.is_utama ? '<span class="badge bg-primary me-1">Foto Ruangan</span> ' : '') + 
    (p.kondisi ? '<span class="badge bg-' + (p.kondisi === 'bersih' ? 'success' : (p.kondisi === 'kurang_bersih' ? 'warning text-dark' : 'danger')) + ' me-1">' + (p.kondisi === 'bersih' ? 'Bersih' : (p.kondisi === 'kurang_bersih' ? 'Kurang Bersih' : 'Kotor')) + '</span> ' : '') +
    (p.catatan ? '<span class="text-light fst-italic">"' + escH(p.catatan) + '"</span>' : '');
  
  document.getElementById('galleryCounter').textContent = `${idx + 1} / ${currentGalleryPhotos.length}`;
  document.getElementById('galleryDownloadBtn').href = p.url;

  // Tombol navigasi prev / next
  const btnPrev = document.getElementById('btnPrevPhoto');
  const btnNext = document.getElementById('btnNextPhoto');
  if (currentGalleryPhotos.length > 1) {
    btnPrev.classList.remove('d-none');
    btnNext.classList.remove('d-none');
  } else {
    btnPrev.classList.add('d-none');
    btnNext.classList.add('d-none');
  }
}

/**
 * Navigasi foto sebelumnya (-1) atau selanjutnya (+1)
 */
function navGalleryPhoto(dir) {
  let newIdx = currentGalleryIndex + dir;
  if (newIdx < 0) newIdx = currentGalleryPhotos.length - 1;
  if (newIdx >= currentGalleryPhotos.length) newIdx = 0;
  viewGalleryPhoto(newIdx);
}

/**
 * Kembali dari single photo view ke tampilan grid
 */
function showGalleryGrid() {
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGridWrap').classList.remove('d-none');
  document.getElementById('galleryBtnBack').classList.add('d-none');
}

/**
 * Buka foto tunggal langsung di modal gallery
 */
function openGallery(url, caption = '') {
  const modalEl = document.getElementById('galleryModal');
  if (!modalEl) {
    window.open(url, '_blank');
    return;
  }
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  currentGalleryPhotos = [{
    url: url,
    aspek: caption || 'Foto Dokumentasi',
    catatan: '',
    is_utama: false,
    kondisi: ''
  }];
  currentGalleryIndex = 0;

  document.getElementById('galleryTitle').innerHTML = '<i class="bi bi-image me-2 text-info"></i>Foto Dokumentasi';
  document.getElementById('galleryLoading').classList.add('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');

  viewGalleryPhoto(0);
  document.getElementById('galleryBtnBack').classList.add('d-none');
  document.getElementById('btnPrevPhoto').classList.add('d-none');
  document.getElementById('btnNextPhoto').classList.add('d-none');

  modal.show();
}

// Alias untuk kompatibilitas showPhoto
window.showPhoto = function(src, caption = '') {
  openGallery(src, caption);
};

function showToast(type, message) {
  Swal.fire({toast:true,position:'top-end',icon:type,title:message,
    showConfirmButton:false,timer:2500,timerProgressBar:true});
}

function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
