<?php
$dataFile = __DIR__ . '/data/songs.json';
$data  = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : ['songs' => []];
$songs = $data['songs'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Music Manager</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#0f0f1a;color:#e0e0e0;height:100vh;display:flex;flex-direction:column;overflow:hidden}

/* TOPBAR */
.topbar{background:#1a1a2e;padding:12px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #2a2a40;flex-shrink:0}
.topbar h1{font-size:18px;font-weight:600;color:#a78bfa;white-space:nowrap}
.topbar h1 span{color:#fff}
.search-wrap{flex:1;position:relative;max-width:400px}
.search-wrap input{width:100%;background:#0f0f1a;border:1px solid #333;border-radius:20px;padding:7px 14px 7px 36px;color:#e0e0e0;font-size:13px;outline:none}
.search-wrap input:focus{border-color:#a78bfa}
.search-wrap::before{content:'🔍';position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:12px}
.btn-upload{background:#7c3aed;color:#fff;border:none;padding:8px 18px;border-radius:20px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;font-weight:500}
.btn-upload:hover{background:#6d28d9}

/* LAYOUT */
.layout{display:flex;flex:1;overflow:hidden}

/* SONG LIST */
.song-panel{width:55%;border-right:1px solid #2a2a40;overflow-y:auto;padding:8px}
.song-panel::-webkit-scrollbar{width:4px}
.song-panel::-webkit-scrollbar-track{background:#0f0f1a}
.song-panel::-webkit-scrollbar-thumb{background:#333}

.song-row{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;cursor:pointer;transition:background .15s;position:relative}
.song-row:hover{background:#1e1e30}
.song-row.active{background:#1e1335;border-left:3px solid #a78bfa}
.song-row.active .snum{color:#a78bfa}

.snum{width:20px;text-align:center;font-size:12px;color:#555;flex-shrink:0}
.song-row.active .snum{display:none}
.song-row.active .splay-icon{display:flex!important}
.splay-icon{display:none;width:20px;justify-content:center;flex-shrink:0}
.splay-icon svg{width:14px;height:14px;fill:#a78bfa;animation:pulse 1s infinite alternate}
@keyframes pulse{from{opacity:.6}to{opacity:1}}

.cover-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#1e1e30;display:flex;align-items:center;justify-content:center;font-size:20px;overflow:hidden}
.cover-thumb img{width:100%;height:100%;object-fit:cover;border-radius:8px}

.sinfo{flex:1;min-width:0}
.stitle{font-size:13px;font-weight:500;color:#e0e0e0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.song-row.active .stitle{color:#a78bfa}
.sartist{font-size:12px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}

.smeta{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0}
.sdur{font-size:12px;color:#555}
.splays{font-size:11px;color:#555}

.del-btn{background:none;border:none;cursor:pointer;color:#555;padding:6px;border-radius:6px;font-size:14px;opacity:0;transition:opacity .15s;flex-shrink:0}
.song-row:hover .del-btn{opacity:1}
.del-btn:hover{color:#f87171;background:rgba(248,113,113,.1)}

.empty-list{text-align:center;padding:60px 20px;color:#444}
.empty-list p{font-size:14px;margin-top:8px}

/* PLAYER PANEL */
.player-panel{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:30px 20px;background:#0a0a16}

.player-cover{width:200px;height:200px;border-radius:16px;background:#1e1e30;display:flex;align-items:center;justify-content:center;font-size:64px;margin-bottom:20px;overflow:hidden;flex-shrink:0;box-shadow:0 8px 32px rgba(124,58,237,.3)}
.player-cover img{width:100%;height:100%;object-fit:cover;border-radius:16px}
.player-cover.spinning{animation:spin 8s linear infinite}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

.player-title{font-size:18px;font-weight:600;color:#fff;text-align:center;margin-bottom:4px;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.player-artist{font-size:13px;color:#888;text-align:center;margin-bottom:20px}

/* PROGRESS */
.progress-area{width:100%;max-width:300px;margin-bottom:16px}
.progress-bar{width:100%;height:4px;background:#2a2a40;border-radius:2px;cursor:pointer;position:relative;margin-bottom:6px}
.progress-bar:hover{height:6px;margin-bottom:4px}
.progress-fill{height:100%;background:linear-gradient(90deg,#7c3aed,#a78bfa);border-radius:2px;pointer-events:none;transition:width .1s linear}
.progress-bar:hover .progress-fill::after{content:'';position:absolute;right:-6px;top:50%;transform:translateY(-50%);width:12px;height:12px;background:#a78bfa;border-radius:50%}
.time-row{display:flex;justify-content:space-between;font-size:11px;color:#555}

/* CONTROLS */
.controls{display:flex;align-items:center;gap:20px;margin-bottom:16px}
.ctrl-btn{background:none;border:none;cursor:pointer;color:#666;padding:8px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:color .15s,background .15s}
.ctrl-btn:hover{color:#a78bfa;background:rgba(167,139,250,.1)}
.ctrl-btn svg{width:20px;height:20px;fill:currentColor}
.play-btn{background:#7c3aed;color:#fff;width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(124,58,237,.4);transition:background .15s,transform .1s}
.play-btn:hover{background:#6d28d9;transform:scale(1.05)}
.play-btn:active{transform:scale(.95)}
.play-btn svg{width:22px;height:22px;fill:#fff}

/* VOLUME */
.vol-row{display:flex;align-items:center;gap:10px;width:100%;max-width:300px}
.vol-icon{color:#555;font-size:16px;flex-shrink:0}
.vol-row input[type=range]{flex:1;-webkit-appearance:none;height:3px;background:#2a2a40;border-radius:2px;outline:none;cursor:pointer}
.vol-row input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:14px;height:14px;background:#a78bfa;border-radius:50%;cursor:pointer}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:none;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(4px)}
.modal-overlay.open{display:flex}
.modal-box{background:#1a1a2e;border:1px solid #2a2a40;border-radius:16px;padding:28px;width:380px;max-width:95vw}
.modal-box h3{font-size:16px;font-weight:600;margin-bottom:20px;color:#a78bfa}
.form-group{margin-bottom:14px}
.form-group label{font-size:12px;color:#888;display:block;margin-bottom:5px}
.form-group input[type=text]{width:100%;background:#0f0f1a;border:1px solid #333;border-radius:8px;padding:9px 12px;color:#e0e0e0;font-size:13px;outline:none}
.form-group input[type=text]:focus{border-color:#a78bfa}
.file-drop{border:1.5px dashed #333;border-radius:10px;padding:18px;text-align:center;font-size:13px;color:#555;cursor:pointer;transition:border-color .15s,color .15s}
.file-drop:hover,.file-drop.has-file{border-color:#a78bfa;color:#a78bfa}
.file-drop input{display:none}
.modal-footer{display:flex;gap:10px;margin-top:20px}
.modal-footer button{flex:1;padding:10px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:500;border:none}
.btn-cancel2{background:#0f0f1a;color:#888;border:1px solid #333!important}
.btn-cancel2:hover{border-color:#555!important;color:#ccc}
.btn-submit{background:#7c3aed;color:#fff}
.btn-submit:hover{background:#6d28d9}
.btn-submit:disabled{background:#444;cursor:not-allowed}

.upload-progress{display:none;margin-top:12px}
.upload-progress .bar{height:4px;background:#2a2a40;border-radius:2px;overflow:hidden}
.upload-progress .fill{height:100%;background:#a78bfa;width:0%;transition:width .3s;animation:indeterminate 1s infinite}
@keyframes indeterminate{0%{width:0%;margin-left:0}50%{width:60%;margin-left:20%}100%{width:0%;margin-left:100%}}
.upload-progress p{font-size:12px;color:#888;text-align:center;margin-top:6px}

.toast{position:fixed;bottom:24px;right:24px;background:#1e1e30;border:1px solid #333;border-radius:10px;padding:12px 18px;font-size:13px;color:#e0e0e0;z-index:999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
.toast.show{opacity:1;transform:translateY(0)}
.toast.success{border-color:#4ade80;color:#4ade80}
.toast.error{border-color:#f87171;color:#f87171}

/* SCROLLBAR */
html::-webkit-scrollbar,body::-webkit-scrollbar{width:0}

@media(max-width:600px){
  .layout{flex-direction:column}
  .song-panel{width:100%;border-right:none;border-bottom:1px solid #2a2a40;height:45vh}
  .player-panel{height:55vh;padding:16px}
  .player-cover{width:120px;height:120px;font-size:40px;margin-bottom:12px}
  .player-title{font-size:15px}
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <h1>🎵 <span>Music</span>Manager</h1>
  <div class="search-wrap">
    <input type="text" id="searchInput" placeholder="Tìm bài hát, nghệ sĩ..." oninput="searchSongs(this.value)">
  </div>
  <button class="btn-upload" onclick="openModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Upload nhạc
  </button>
</div>

<!-- LAYOUT -->
<div class="layout">
  <!-- DANH SÁCH -->
  <div class="song-panel" id="songPanel">
    <?php if (empty($songs)): ?>
    <div class="empty-list">
      <div style="font-size:48px">🎶</div>
      <p>Chưa có bài hát nào.<br>Nhấn <strong>Upload nhạc</strong> để bắt đầu!</p>
    </div>
    <?php else: ?>
    <?php foreach ($songs as $i => $song):
      $coverHtml = $song['cover']
        ? '<img src="'.htmlspecialchars($song['cover']).'" alt="cover" onerror="this.style.display=\'none\'">'
        : '🎵';
    ?>
    <div class="song-row"
         data-id="<?= htmlspecialchars($song['id']) ?>"
         data-audio="<?= htmlspecialchars($song['audio']) ?>"
         data-cover="<?= htmlspecialchars($song['cover']) ?>"
         data-title="<?= htmlspecialchars($song['title']) ?>"
         data-artist="<?= htmlspecialchars($song['artist']) ?>"
         onclick="playSong(this)">
      <div class="snum"><?= $i + 1 ?></div>
      <div class="splay-icon">
        <svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
      </div>
      <div class="cover-thumb"><?= $coverHtml ?></div>
      <div class="sinfo">
        <div class="stitle"><?= htmlspecialchars($song['title']) ?></div>
        <div class="sartist"><?= htmlspecialchars($song['artist']) ?></div>
      </div>
      <div class="smeta">
        <span class="sdur" id="dur-<?= htmlspecialchars($song['id']) ?>"><?= htmlspecialchars($song['duration'] ?: '—') ?></span>
        <span class="splays"><?= number_format($song['plays']) ?> lượt</span>
      </div>
      <button class="del-btn" onclick="deleteSong(event, '<?= htmlspecialchars($song['id']) ?>')" title="Xóa">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
      </button>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- PLAYER -->
  <div class="player-panel">
    <div class="player-cover" id="playerCover">🎵</div>
    <div class="player-title" id="playerTitle">Chọn bài hát</div>
    <div class="player-artist" id="playerArtist">— —</div>

    <div class="progress-area">
      <div class="progress-bar" id="progressBar" onclick="seekTo(event)">
        <div class="progress-fill" id="progressFill" style="width:0%"></div>
      </div>
      <div class="time-row">
        <span id="curTime">0:00</span>
        <span id="totTime">0:00</span>
      </div>
    </div>

    <div class="controls">
      <button class="ctrl-btn" onclick="prevSong()" title="Bài trước">
        <svg viewBox="0 0 24 24"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5" stroke="currentColor" stroke-width="2"/></svg>
      </button>
      <button class="play-btn" id="playBtn" onclick="togglePlay()" title="Phát/Dừng">
        <svg id="playIcon" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      </button>
      <button class="ctrl-btn" onclick="nextSong()" title="Bài tiếp">
        <svg viewBox="0 0 24 24"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2"/></svg>
      </button>
    </div>

    <div class="vol-row">
      <span class="vol-icon">🔈</span>
      <input type="range" id="volSlider" min="0" max="100" value="80" step="1" oninput="setVolume(this.value)">
      <span class="vol-icon">🔊</span>
    </div>

    <!-- Audio element ẩn -->
    <audio id="audioEl" preload="metadata"></audio>
  </div>
</div>

<!-- MODAL UPLOAD -->
<div class="modal-overlay" id="uploadModal">
  <div class="modal-box">
    <h3>🎵 Thêm bài hát mới</h3>
    <div class="form-group">
      <label>Tên bài hát *</label>
      <input type="text" id="inputTitle" placeholder="VD: Nơi này có anh" maxlength="200">
    </div>
    <div class="form-group">
      <label>Nghệ sĩ</label>
      <input type="text" id="inputArtist" placeholder="VD: Sơn Tùng M-TP" maxlength="100">
    </div>
    <div class="form-group">
      <label>File nhạc * (MP3, WAV, OGG, M4A)</label>
      <div class="file-drop" id="audioDrop" onclick="document.getElementById('audioFile').click()">
        <div style="font-size:28px;margin-bottom:6px">🎵</div>
        <span id="audioLabel">Nhấn để chọn file nhạc</span>
        <input type="file" id="audioFile" accept=".mp3,.wav,.ogg,.m4a,.flac,.aac,audio/*" onchange="onAudioSelected(this)">
      </div>
    </div>
    <div class="form-group">
      <label>Ảnh bìa (tuỳ chọn)</label>
      <div class="file-drop" id="coverDrop" onclick="document.getElementById('coverFile').click()">
        <div style="font-size:28px;margin-bottom:6px">🖼️</div>
        <span id="coverLabel">Nhấn để chọn ảnh bìa</span>
        <input type="file" id="coverFile" accept="image/*" onchange="onCoverSelected(this)">
      </div>
    </div>
    <div class="upload-progress" id="uploadProgress">
      <div class="bar"><div class="fill" id="progressBar2"></div></div>
      <p>Đang upload...</p>
    </div>
    <div class="modal-footer">
      <button class="modal-footer btn-cancel2" onclick="closeModal()">Hủy</button>
      <button class="btn-submit" id="submitBtn" onclick="submitUpload()">✅ Thêm vào thư viện</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
// ===== AUDIO ENGINE =====
const audio = document.getElementById('audioEl');
let currentId = null;
let currentRow = null;
let songs = [];  // Cache bài hát đang hiển thị

// Load tất cả song rows
function getAllRows() {
  return Array.from(document.querySelectorAll('.song-row'));
}

// Phát bài hát từ row element
function playSong(row) {
  const audioSrc = row.dataset.audio;
  if (!audioSrc) { showToast('File nhạc không hợp lệ', 'error'); return; }

  // Bỏ active cũ
  document.querySelectorAll('.song-row.active').forEach(r => r.classList.remove('active'));
  row.classList.add('active');
  currentRow = row;
  currentId  = row.dataset.id;

  // Cập nhật player
  const title  = row.dataset.title  || 'Không tên';
  const artist = row.dataset.artist || 'Unknown';
  const cover  = row.dataset.cover  || '';

  document.getElementById('playerTitle').textContent  = title;
  document.getElementById('playerArtist').textContent = artist;

  const coverEl = document.getElementById('playerCover');
  if (cover) {
    coverEl.innerHTML = `<img src="${cover}" alt="cover" onerror="this.parentElement.innerHTML='🎵'">`;
  } else {
    coverEl.textContent = '🎵';
  }

  // Set source & play — dùng đường dẫn tương đối từ gốc site
  audio.src = audioSrc;
  audio.load();
  audio.play().then(() => {
    setPlayIcon(true);
    document.getElementById('playerCover').classList.add('spinning');
  }).catch(err => {
    console.error('Play error:', err);
    showToast('Không thể phát file này. Kiểm tra lại định dạng.', 'error');
    setPlayIcon(false);
  });

  // Tăng lượt nghe
  fetch('api.php?action=play&id=' + encodeURIComponent(currentId)).catch(()=>{});
}

function setPlayIcon(isPlaying) {
  const icon = document.getElementById('playIcon');
  if (isPlaying) {
    icon.innerHTML = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
  } else {
    icon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"/>';
  }
}

function togglePlay() {
  if (!audio.src || audio.src === window.location.href) {
    // Chưa có bài nào — phát bài đầu
    const first = document.querySelector('.song-row');
    if (first) playSong(first);
    return;
  }
  if (audio.paused) {
    audio.play().then(()=>{ setPlayIcon(true); document.getElementById('playerCover').classList.add('spinning'); });
  } else {
    audio.pause();
    setPlayIcon(false);
    document.getElementById('playerCover').classList.remove('spinning');
  }
}

function prevSong() {
  if (!currentRow) return;
  const rows = getAllRows();
  const idx  = rows.indexOf(currentRow);
  if (idx > 0) playSong(rows[idx - 1]);
}
function nextSong() {
  if (!currentRow) return;
  const rows = getAllRows();
  const idx  = rows.indexOf(currentRow);
  if (idx < rows.length - 1) playSong(rows[idx + 1]);
}

// Progress
audio.addEventListener('timeupdate', () => {
  if (!audio.duration || isNaN(audio.duration)) return;
  const pct = audio.currentTime / audio.duration * 100;
  document.getElementById('progressFill').style.width = pct + '%';
  document.getElementById('curTime').textContent = fmtTime(audio.currentTime);
  document.getElementById('totTime').textContent = fmtTime(audio.duration);
});

audio.addEventListener('loadedmetadata', () => {
  document.getElementById('totTime').textContent = fmtTime(audio.duration);
  // Cập nhật duration trong danh sách
  if (currentId) {
    const durEl = document.getElementById('dur-' + currentId);
    if (durEl) durEl.textContent = fmtTime(audio.duration);
    // Lưu duration lên server
    fetch('save_duration.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'id=' + encodeURIComponent(currentId) + '&duration=' + encodeURIComponent(fmtTime(audio.duration))
    }).catch(()=>{});
  }
});

audio.addEventListener('ended', () => {
  setPlayIcon(false);
  document.getElementById('playerCover').classList.remove('spinning');
  nextSong();
});

audio.addEventListener('error', (e) => {
  setPlayIcon(false);
  document.getElementById('playerCover').classList.remove('spinning');
  showToast('Lỗi phát nhạc: ' + (audio.error?.message || 'File không hợp lệ'), 'error');
});

function seekTo(e) {
  if (!audio.duration || isNaN(audio.duration)) return;
  const rect = e.currentTarget.getBoundingClientRect();
  const pct  = (e.clientX - rect.left) / rect.width;
  audio.currentTime = pct * audio.duration;
}

function setVolume(v) {
  audio.volume = v / 100;
}

function fmtTime(s) {
  if (isNaN(s)) return '0:00';
  const m = Math.floor(s / 60);
  const sec = Math.floor(s % 60);
  return m + ':' + (sec < 10 ? '0' : '') + sec;
}

// ===== SEARCH =====
function searchSongs(q) {
  q = q.trim().toLowerCase();
  document.querySelectorAll('.song-row').forEach(row => {
    const title  = (row.dataset.title  || '').toLowerCase();
    const artist = (row.dataset.artist || '').toLowerCase();
    row.style.display = (!q || title.includes(q) || artist.includes(q)) ? '' : 'none';
  });
}

// ===== DELETE =====
async function deleteSong(e, id) {
  e.stopPropagation();
  if (!confirm('Bạn có chắc muốn xóa bài hát này không?')) return;

  const res  = await fetch('delete.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'id=' + encodeURIComponent(id)
  });
  const data = await res.json();

  if (data.success) {
    // Nếu đang phát bài này thì dừng lại
    if (currentId === id) {
      audio.pause();
      audio.src = '';
      currentId  = null;
      currentRow = null;
      setPlayIcon(false);
      document.getElementById('playerTitle').textContent  = 'Chọn bài hát';
      document.getElementById('playerArtist').textContent = '— —';
      document.getElementById('playerCover').textContent  = '🎵';
      document.getElementById('progressFill').style.width = '0%';
      document.getElementById('curTime').textContent = '0:00';
      document.getElementById('totTime').textContent = '0:00';
    }
    // Xóa row khỏi DOM
    const row = document.querySelector(`.song-row[data-id="${id}"]`);
    if (row) row.remove();
    // Đánh lại số thứ tự
    document.querySelectorAll('.song-row .snum').forEach((el, i) => el.textContent = i + 1);
    showToast('Đã xóa bài hát!', 'success');
  } else {
    showToast('Lỗi: ' + (data.message || 'Không thể xóa'), 'error');
  }
}

// ===== UPLOAD MODAL =====
function openModal() { document.getElementById('uploadModal').classList.add('open'); }
function closeModal() {
  document.getElementById('uploadModal').classList.remove('open');
  document.getElementById('inputTitle').value  = '';
  document.getElementById('inputArtist').value = '';
  document.getElementById('audioLabel').textContent = 'Nhấn để chọn file nhạc';
  document.getElementById('coverLabel').textContent = 'Nhấn để chọn ảnh bìa';
  document.getElementById('audioDrop').classList.remove('has-file');
  document.getElementById('coverDrop').classList.remove('has-file');
  document.getElementById('audioFile').value = '';
  document.getElementById('coverFile').value = '';
  document.getElementById('uploadProgress').style.display = 'none';
  document.getElementById('submitBtn').disabled = false;
}

function onAudioSelected(input) {
  if (input.files[0]) {
    document.getElementById('audioLabel').textContent = '✅ ' + input.files[0].name;
    document.getElementById('audioDrop').classList.add('has-file');
    // Tự điền tên bài hát nếu trống
    if (!document.getElementById('inputTitle').value.trim()) {
      const name = input.files[0].name.replace(/\.[^.]+$/, '').replace(/[_-]/g, ' ');
      document.getElementById('inputTitle').value = name;
    }
  }
}

function onCoverSelected(input) {
  if (input.files[0]) {
    document.getElementById('coverLabel').textContent = '✅ ' + input.files[0].name;
    document.getElementById('coverDrop').classList.add('has-file');
  }
}

async function submitUpload() {
  const title = document.getElementById('inputTitle').value.trim();
  const audioFile = document.getElementById('audioFile').files[0];

  if (!title) { showToast('Vui lòng nhập tên bài hát!', 'error'); return; }
  if (!audioFile) { showToast('Vui lòng chọn file nhạc!', 'error'); return; }

  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  document.getElementById('uploadProgress').style.display = 'block';

  const formData = new FormData();
  formData.append('title',  title);
  formData.append('artist', document.getElementById('inputArtist').value.trim());
  formData.append('audio',  audioFile);
  const coverFile = document.getElementById('coverFile').files[0];
  if (coverFile) formData.append('cover', coverFile);

  try {
    const res  = await fetch('upload.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      showToast('Upload thành công! 🎉', 'success');
      closeModal();
      setTimeout(() => location.reload(), 800);
    } else {
      showToast('Lỗi: ' + (data.message || 'Upload thất bại'), 'error');
      btn.disabled = false;
      document.getElementById('uploadProgress').style.display = 'none';
    }
  } catch (err) {
    showToast('Lỗi kết nối: ' + err.message, 'error');
    btn.disabled = false;
    document.getElementById('uploadProgress').style.display = 'none';
  }
}

// ===== TOAST =====
let toastTimer;
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
  if (e.target.tagName === 'INPUT') return;
  if (e.code === 'Space') { e.preventDefault(); togglePlay(); }
  if (e.code === 'ArrowRight') nextSong();
  if (e.code === 'ArrowLeft') prevSong();
});

// Click ngoài modal để đóng
document.getElementById('uploadModal').addEventListener('click', (e) => {
  if (e.target === document.getElementById('uploadModal')) closeModal();
});
</script>
</body>
</html>
