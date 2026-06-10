<?php
$dataFile = __DIR__ . '/data/songs.json';
$raw  = file_exists($dataFile) ? file_get_contents($dataFile) : '{"songs":[]}';
$data = json_decode($raw, true) ?: ['songs' => []];
$songs = $data['songs'] ?? [];
$songsJson = json_encode($songs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SoundWave — Music Manager</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap');
:root {
  --bg0:#080810; --bg1:#0e0e1c; --bg2:#13132b; --bg3:#1a1a35;
  --accent:#7b5ea7; --accent2:#a855f7; --neon:#c084fc;
  --cyan:#22d3ee; --pink:#f472b6; --green:#4ade80;
  --text0:#f8f8ff; --text1:#b4b4d0; --text2:#6b6b90;
  --border:#ffffff0f; --card:#ffffff06;
  --radius:14px; --radius-sm:8px;
  --font-head:'Space Grotesk',sans-serif; --font:'Inter',sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;background:var(--bg0);color:var(--text0);font-family:var(--font);overflow:hidden}
button{cursor:pointer;font-family:var(--font);border:none;outline:none}
input,textarea{font-family:var(--font);outline:none}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:3px;height:3px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--accent);border-radius:2px}

/* ── LAYOUT ── */
.app{display:grid;grid-template-rows:56px 1fr 96px;grid-template-columns:64px 280px 1fr;height:100vh;position:relative}

/* ── TOPBAR ── */
.topbar{grid-column:1/-1;display:flex;align-items:center;gap:12px;padding:0 20px;background:var(--bg1);border-bottom:1px solid var(--border);z-index:10}
.logo{font-family:var(--font-head);font-size:17px;font-weight:700;background:linear-gradient(135deg,var(--neon),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;white-space:nowrap;margin-right:4px}
.logo svg{display:inline-block;margin-right:6px;vertical-align:-3px}
.search-box{flex:1;max-width:420px;position:relative}
.search-box input{width:100%;background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:7px 14px 7px 36px;color:var(--text0);font-size:13px;transition:border-color .2s}
.search-box input:focus{border-color:var(--accent)}
.search-box input::placeholder{color:var(--text2)}
.search-box .ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text2);pointer-events:none}
.topbar-actions{margin-left:auto;display:flex;gap:8px;align-items:center}
.btn-upload{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;padding:7px 16px;border-radius:20px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:6px;transition:opacity .15s,transform .1s}
.btn-upload:hover{opacity:.9;transform:scale(1.02)}
.view-toggle{display:flex;gap:2px;background:var(--bg2);border-radius:8px;padding:3px}
.view-toggle button{background:none;padding:5px 8px;border-radius:6px;color:var(--text2);transition:all .15s}
.view-toggle button.active{background:var(--accent);color:#fff}

/* ── SIDEBAR NAV ── */
.sidenav{grid-row:2/3;background:var(--bg1);border-right:1px solid var(--border);display:flex;flex-direction:column;align-items:center;padding:16px 0;gap:4px}
.nav-btn{width:42px;height:42px;border-radius:var(--radius-sm);background:none;color:var(--text2);display:flex;align-items:center;justify-content:center;transition:all .15s;position:relative}
.nav-btn:hover{background:var(--bg2);color:var(--text0)}
.nav-btn.active{background:linear-gradient(135deg,var(--accent)22,var(--accent2)11);color:var(--neon)}
.nav-btn::after{content:attr(data-tip);position:absolute;left:54px;background:var(--bg3);color:var(--text0);font-size:11px;padding:4px 10px;border-radius:6px;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .15s;z-index:50}
.nav-btn:hover::after{opacity:1}
.nav-sep{width:28px;height:1px;background:var(--border);margin:6px 0}

/* ── PANELS ── */
.panel{grid-row:2/3;overflow:hidden;display:flex;flex-direction:column}
.left-panel{background:var(--bg1);border-right:1px solid var(--border);width:280px}
.main-panel{flex:1;background:var(--bg0);overflow:hidden;display:flex;flex-direction:column}

/* ── PLAYLIST PANEL ── */
.panel-header{padding:14px 16px 10px;display:flex;align-items:center;gap:8px}
.panel-header h3{font-size:13px;font-weight:600;color:var(--text1);text-transform:uppercase;letter-spacing:.08em;flex:1}
.panel-tabs{display:flex;gap:2px;padding:0 12px 10px;border-bottom:1px solid var(--border)}
.ptab{flex:1;padding:6px;font-size:12px;font-weight:500;border-radius:var(--radius-sm);background:none;color:var(--text2);transition:all .15s}
.ptab.active{background:var(--bg2);color:var(--neon)}
.song-scroll{flex:1;overflow-y:auto;padding:8px}
.song-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--radius-sm);cursor:pointer;transition:background .12s;position:relative;user-select:none}
.song-item:hover{background:var(--card)}
.song-item.active{background:linear-gradient(90deg,var(--accent)18,transparent)}
.song-item.active::before{content:'';position:absolute;left:0;top:8px;bottom:8px;width:3px;background:linear-gradient(180deg,var(--neon),var(--accent2));border-radius:0 2px 2px 0}
.snum{width:18px;font-size:11px;color:var(--text2);text-align:center;flex-shrink:0}
.song-item.active .snum{display:none}
.playing-anim{display:none;width:18px;flex-shrink:0;align-items:flex-end;justify-content:center;gap:2px;height:14px}
.song-item.active .playing-anim{display:flex}
.bar-anim{width:3px;background:var(--neon);border-radius:2px;animation:barUp 0.8s ease infinite alternate}
.bar-anim:nth-child(1){height:6px;animation-delay:0s}
.bar-anim:nth-child(2){height:10px;animation-delay:.2s}
.bar-anim:nth-child(3){height:14px;animation-delay:.1s}
.song-item.paused .bar-anim{animation-play-state:paused}
@keyframes barUp{from{transform:scaleY(.3)}to{transform:scaleY(1)}}
.scover{width:38px;height:38px;border-radius:6px;object-fit:cover;flex-shrink:0;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:16px;overflow:hidden;position:relative}
.scover img{width:100%;height:100%;object-fit:cover}
.sinfo{flex:1;min-width:0}
.stitle{font-size:13px;font-weight:500;color:var(--text0);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.song-item.active .stitle{color:var(--neon)}
.sartist{font-size:11px;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.smeta{display:flex;flex-direction:column;align-items:flex-end;gap:3px;flex-shrink:0}
.sdur{font-size:11px;color:var(--text2)}
.like-dot{width:6px;height:6px;border-radius:50%;background:var(--pink);opacity:0;transition:opacity .15s}
.liked .like-dot{opacity:1}
.item-actions{position:absolute;right:8px;top:50%;transform:translateY(-50%);display:flex;gap:2px;opacity:0;transition:opacity .15s}
.song-item:hover .item-actions{opacity:1}
.song-item:hover .smeta{opacity:0}
.ia-btn{width:26px;height:26px;border-radius:6px;background:var(--bg2);color:var(--text2);display:flex;align-items:center;justify-content:center;transition:all .15s;font-size:13px}
.ia-btn:hover{background:var(--accent);color:#fff}
.ia-btn.heart.liked{background:#f472b633;color:var(--pink)}

/* ── MAIN CONTENT ── */
.main-content{flex:1;overflow:hidden;position:relative}
/* View: grid */
.view-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;padding:20px;overflow-y:auto;height:100%}
.grid-card{background:var(--bg2);border-radius:var(--radius);padding:12px;cursor:pointer;transition:all .2s;border:1px solid transparent;position:relative;overflow:hidden}
.grid-card:hover{border-color:var(--accent)55;transform:translateY(-2px)}
.grid-card.active{border-color:var(--neon)88}
.grid-card::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,var(--bg0)cc);pointer-events:none;z-index:1}
.gc-cover{width:100%;aspect-ratio:1;border-radius:var(--radius-sm);object-fit:cover;background:var(--bg3);font-size:40px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;overflow:hidden}
.gc-cover img{width:100%;height:100%;object-fit:cover}
.gc-info{position:relative;z-index:2}
.gc-title{font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gc-artist{font-size:11px;color:var(--text2);margin-top:2px}
.gc-play{position:absolute;right:12px;bottom:42px;width:36px;height:36px;border-radius:50%;background:var(--neon);color:#000;display:flex;align-items:center;justify-content:center;z-index:3;opacity:0;transform:translateY(4px);transition:all .2s}
.grid-card:hover .gc-play{opacity:1;transform:translateY(0)}
/* View: list */
.view-list{display:flex;flex-direction:column;padding:16px;overflow-y:auto;height:100%;gap:3px}
.list-row{display:flex;align-items:center;gap:14px;padding:10px 14px;border-radius:var(--radius-sm);cursor:pointer;transition:background .12s;border:1px solid transparent}
.list-row:hover{background:var(--card)}
.list-row.active{background:var(--accent)18;border-color:var(--accent)33}
.list-row .lnum{width:24px;font-size:12px;color:var(--text2);text-align:center;flex-shrink:0}
.list-row .lcover{width:42px;height:42px;border-radius:8px;background:var(--bg3);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:18px;overflow:hidden}
.list-row .lcover img{width:100%;height:100%;object-fit:cover}
.list-row .linfo{flex:1;min-width:0}
.list-row .ltitle{font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.list-row.active .ltitle{color:var(--neon)}
.list-row .lartist{font-size:12px;color:var(--text2);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.list-row .lplays{width:70px;font-size:12px;color:var(--text2);text-align:right;flex-shrink:0}
.list-row .ldur{width:44px;font-size:12px;color:var(--text2);text-align:right;flex-shrink:0}
.list-row .lactions{display:flex;gap:4px;opacity:0;transition:opacity .15s}
.list-row:hover .lactions{opacity:1}
.list-row:hover .lplays,.list-row:hover .ldur{opacity:0}
.lact-btn{width:28px;height:28px;border-radius:6px;background:var(--bg2);color:var(--text2);display:flex;align-items:center;justify-content:center;font-size:13px}
.lact-btn:hover{background:var(--accent);color:#fff}
.lact-btn.heart.liked{background:#f472b633;color:var(--pink)}
.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text2);gap:12px;text-align:center;padding:40px}
.empty-state .big-ico{font-size:64px;opacity:.3}
.empty-state h3{font-size:16px;color:var(--text1)}
.empty-state p{font-size:13px;line-height:1.6}

/* ── VISUALIZER ── */
.visualizer-bar{height:3px;background:var(--bg1);position:relative;overflow:hidden;flex-shrink:0}
.viz-canvas-wrap{position:absolute;inset:0;height:60px;bottom:-57px;pointer-events:none}
canvas#vizCanvas{width:100%;height:60px;opacity:.55}

/* ── BOTTOM PLAYER ── */
.player-bar{grid-column:1/-1;background:var(--bg1);border-top:1px solid var(--border);display:grid;grid-template-columns:1fr auto 1fr;align-items:center;padding:0 20px;gap:20px;position:relative;z-index:10}
/* Left: song info */
.pb-info{display:flex;align-items:center;gap:12px;min-width:0}
.pb-cover{width:48px;height:48px;border-radius:8px;background:var(--bg3);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px;overflow:hidden;position:relative}
.pb-cover img{width:100%;height:100%;object-fit:cover}
.pb-cover .vinyl{position:absolute;inset:0;border-radius:50%;border:2px solid var(--neon)33;animation:spinSlow 6s linear infinite;display:none}
.playing .pb-cover .vinyl{display:block}
@keyframes spinSlow{to{transform:rotate(360deg)}}
.pb-text{min-width:0}
.pb-title{font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px}
.pb-artist{font-size:12px;color:var(--text2);margin-top:2px}
.pb-like{background:none;color:var(--text2);font-size:18px;flex-shrink:0;transition:color .15s;padding:4px}
.pb-like:hover,.pb-like.liked{color:var(--pink)}

/* Center: controls */
.pb-controls{display:flex;flex-direction:column;align-items:center;gap:8px;min-width:320px}
.ctrl-row{display:flex;align-items:center;gap:12px}
.ctrl-btn{background:none;color:var(--text2);padding:6px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all .15s}
.ctrl-btn:hover{color:var(--text0)}
.ctrl-btn.active{color:var(--neon)}
.ctrl-btn svg{width:18px;height:18px;fill:currentColor}
.play-btn{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--neon),var(--accent2));color:#000;display:flex;align-items:center;justify-content:center;transition:transform .1s,box-shadow .15s;box-shadow:0 4px 20px var(--neon)44}
.play-btn:hover{transform:scale(1.08);box-shadow:0 6px 28px var(--neon)66}
.play-btn:active{transform:scale(.95)}
.play-btn svg{width:20px;height:20px;fill:#000}
.progress-wrap{width:100%;display:flex;align-items:center;gap:8px}
.time-label{font-size:11px;color:var(--text2);width:32px;flex-shrink:0;font-variant-numeric:tabular-nums}
.time-label:last-child{text-align:right}
.progress-track{flex:1;height:4px;background:var(--bg3);border-radius:2px;cursor:pointer;position:relative;transition:height .15s}
.progress-track:hover{height:6px}
.progress-fill{height:100%;background:linear-gradient(90deg,var(--accent2),var(--neon));border-radius:2px;pointer-events:none;transition:width .1s linear}
.progress-track::after{content:'';position:absolute;right:calc(100% - var(--p, 0%));top:50%;transform:translateY(-50%);width:12px;height:12px;background:var(--neon);border-radius:50%;opacity:0;transition:opacity .15s;pointer-events:none;margin-right:-6px}
.progress-track:hover::after{opacity:1}

/* Right: extra controls */
.pb-extra{display:flex;align-items:center;gap:8px;justify-content:flex-end}
.vol-wrap{display:flex;align-items:center;gap:8px;flex:1;max-width:160px}
.vol-icon{color:var(--text2);flex-shrink:0}
.vu-meter{opacity:0;transform:scaleX(.95);transition:opacity .3s,transform .3s}
.playing .vu-meter{opacity:1;transform:scaleX(1)}
input[type=range]{-webkit-appearance:none;height:3px;background:var(--bg3);border-radius:2px;cursor:pointer;width:100%;outline:none}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:12px;height:12px;background:var(--neon);border-radius:50%}
input[type=range]:focus{outline:none}
.lyrics-btn,.eq-btn{background:none;color:var(--text2);padding:6px;border-radius:6px;font-size:13px;transition:color .15s}
.lyrics-btn:hover,.eq-btn:hover,.lyrics-btn.active,.eq-btn.active{color:var(--neon)}

/* ── LED VU METER (2 dãy dọc) ── */
.vu-meter{display:flex;align-items:center;gap:6px;padding:4px 10px;background:var(--bg2);border-radius:8px;border:1px solid var(--border);flex-shrink:0}
.vu-ch{display:flex;flex-direction:column;gap:2px;align-items:center}
.vu-ch-label{font-size:8px;color:var(--text2);letter-spacing:.06em;margin-top:2px}
.vu-dot{width:8px;height:6px;border-radius:2px;background:#1a1a2e;flex-shrink:0}
.vu-dot.on-green {background:#4ade80;box-shadow:0 0 6px #4ade80cc}
.vu-dot.on-yellow{background:#facc15;box-shadow:0 0 6px #facc15cc}
.vu-dot.on-orange{background:#fb923c;box-shadow:0 0 6px #fb923ccc}
.vu-dot.on-red   {background:#f87171;box-shadow:0 0 8px #f87171ee}

/* ── LYRICS PANEL ── */
.lyrics-panel{position:absolute;right:0;top:0;bottom:0;width:340px;background:var(--bg1);border-left:1px solid var(--border);display:flex;flex-direction:column;transform:translateX(100%);transition:transform .3s cubic-bezier(.4,0,.2,1);z-index:20}
.lyrics-panel.open{transform:translateX(0)}
.lp-head{padding:16px;display:flex;align-items:center;border-bottom:1px solid var(--border)}
.lp-head h4{flex:1;font-size:13px;font-weight:600;color:var(--text1);text-transform:uppercase;letter-spacing:.06em}
.lp-close{background:none;color:var(--text2);font-size:18px;padding:4px;border-radius:6px;transition:color .15s}
.lp-close:hover{color:var(--text0)}
.lp-body{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:8px}
.lyrics-text{font-size:14px;line-height:1.9;color:var(--text1);white-space:pre-wrap;flex:1}
.lyrics-text em{color:var(--text2);font-size:12px;font-style:normal}
.lp-edit-toggle{background:var(--bg2);color:var(--text1);padding:8px 14px;border-radius:var(--radius-sm);font-size:12px;font-weight:500;transition:all .15s;border:1px solid var(--border);margin:0 16px 12px;flex-shrink:0}
.lp-edit-toggle:hover{border-color:var(--accent)}
.lyrics-edit{display:none;flex:1;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px;color:var(--text0);font-size:13px;line-height:1.7;resize:none;transition:border-color .15s}
.lyrics-edit:focus{border-color:var(--accent)}
.lp-save{display:none;background:var(--accent);color:#fff;padding:8px 20px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;margin:8px 16px 16px;transition:opacity .15s}
.lp-save:hover{opacity:.9}

/* ── EQUALIZER PANEL ── */
.eq-panel{position:absolute;bottom:96px;right:20px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:16px;width:280px;display:none;z-index:30;box-shadow:0 8px 40px #00000080}
.eq-panel.open{display:block}
.eq-head{display:flex;align-items:center;margin-bottom:14px}
.eq-head h4{flex:1;font-size:13px;font-weight:600}
.eq-reset{font-size:11px;color:var(--accent2);background:none;transition:color .15s}
.eq-reset:hover{color:var(--neon)}
.eq-close2{background:none;color:var(--text2);font-size:16px;padding:2px 6px;border-radius:4px}
.eq-bands{display:flex;gap:10px;align-items:flex-end;justify-content:center;height:100px}
.eq-band{display:flex;flex-direction:column;align-items:center;gap:6px}
.eq-band label{font-size:10px;color:var(--text2)}
.eq-band input[type=range]{writing-mode:vertical-lr;direction:rtl;-webkit-appearance:slider-vertical;width:20px;height:72px;background:transparent;cursor:pointer}
.eq-band input[type=range]::-webkit-slider-runnable-track{width:3px;background:var(--bg3);border-radius:2px}
.eq-band input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:12px;height:12px;background:var(--neon);border-radius:50%;margin-right:-4px}
.eq-presets{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px;padding-top:12px;border-top:1px solid var(--border)}
.eq-preset{background:var(--bg3);color:var(--text2);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500;transition:all .15s;border:1px solid transparent}
.eq-preset:hover,.eq-preset.active{background:var(--accent)33;color:var(--neon);border-color:var(--accent)55}

/* ── UPLOAD MODAL ── */
.modal-overlay{position:fixed;inset:0;background:#000000bb;display:none;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(8px)}
.modal-overlay.open{display:flex}
.modal-box{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:28px;width:420px;max-width:95vw;max-height:90vh;overflow-y:auto}
.modal-box h3{font-family:var(--font-head);font-size:18px;font-weight:700;margin-bottom:20px;background:linear-gradient(135deg,var(--neon),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.form-grp{margin-bottom:14px}
.form-grp label{font-size:12px;color:var(--text2);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em}
.form-grp input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;color:var(--text0);font-size:13px;transition:border-color .2s}
.form-grp input:focus{border-color:var(--accent2)}
.drop-zone{border:1.5px dashed var(--border);border-radius:var(--radius);padding:24px 16px;text-align:center;cursor:pointer;transition:all .2s}
.drop-zone:hover,.drop-zone.has-file{border-color:var(--accent2);background:var(--accent)08}
.drop-zone .dz-icon{font-size:32px;margin-bottom:8px;display:block}
.drop-zone p{font-size:13px;color:var(--text2)}
.drop-zone.has-file p{color:var(--neon)}
.drop-zone input{display:none}
.modal-footer{display:flex;gap:10px;margin-top:20px}
.modal-footer button{flex:1;padding:11px;border-radius:var(--radius-sm);font-size:13px;font-weight:600}
.btn-cancel{background:var(--bg3);color:var(--text2);border:1px solid var(--border);transition:all .15s}
.btn-cancel:hover{border-color:var(--text2);color:var(--text0)}
.btn-go{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;transition:opacity .15s}
.btn-go:hover{opacity:.9}
.btn-go:disabled{background:var(--bg3);color:var(--text2);pointer-events:none}
.upload-bar{height:3px;background:var(--bg3);border-radius:2px;margin-top:14px;overflow:hidden;display:none}
.upload-bar .fill{height:100%;background:linear-gradient(90deg,var(--accent2),var(--neon));animation:slide 1s ease infinite}
@keyframes slide{0%{width:0%;margin-left:0}50%{width:60%;margin-left:20%}100%{width:0%;margin-left:100%}}

/* ── TOAST ── */
.toast{position:fixed;bottom:112px;left:50%;transform:translateX(-50%) translateY(10px);background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 20px;font-size:13px;white-space:nowrap;z-index:999;opacity:0;transition:all .25s;pointer-events:none}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast.ok{border-color:var(--green)66;color:var(--green)}
.toast.err{border-color:var(--pink)66;color:var(--pink)}

/* ── CONTEXT MENU ── */
.ctx-menu{position:fixed;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px;z-index:200;min-width:170px;box-shadow:0 8px 30px #000a;display:none}
.ctx-menu.open{display:block}
.ctx-item{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:6px;font-size:13px;color:var(--text1);cursor:pointer;transition:background .1s}
.ctx-item:hover{background:var(--bg3);color:var(--text0)}
.ctx-item.danger{color:#f87171}
.ctx-item.danger:hover{background:#f8717115}
.ctx-sep{height:1px;background:var(--border);margin:4px 0}

/* ── MOBILE ── */
@media(max-width:768px){
  .app{grid-template-columns:1fr;grid-template-rows:52px 1fr 1fr 80px}
  .sidenav{display:none}
  .left-panel{width:100%;height:100%;border-right:none;border-bottom:1px solid var(--border)}
  .main-panel{display:none}
  .pb-extra .vol-wrap,.lyrics-btn,.eq-btn{display:none}
  .pb-info .pb-title{max-width:120px}
  .pb-controls{min-width:220px}
  .eq-panel{right:8px;bottom:88px;width:calc(100vw - 16px)}
}
@media(min-width:769px) and (max-width:1100px){
  .app{grid-template-columns:64px 240px 1fr}
  .left-panel{width:240px}
}
</style>
</head>
<body>
<div class="app" id="app">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="logo">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18V5l12-2v13" stroke="url(#lg)" stroke-width="2" stroke-linecap="round"/><circle cx="6" cy="18" r="3" fill="url(#lg)"/><circle cx="18" cy="16" r="3" fill="url(#lg)"/><defs><linearGradient id="lg" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse"><stop stop-color="#c084fc"/><stop offset="1" stop-color="#22d3ee"/></linearGradient></defs></svg>
      SoundWave
    </div>
    <div class="search-box">
      <svg class="ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" id="searchInput" placeholder="Tìm bài hát, nghệ sĩ..." oninput="filterSongs(this.value)">
    </div>
    <div class="topbar-actions">
      <div class="view-toggle">
        <button id="btnGrid" class="active" onclick="setView('grid')" title="Grid view">⊞</button>
        <button id="btnList" onclick="setView('list')" title="List view">≡</button>
      </div>
      <button class="btn-upload" onclick="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload nhạc
      </button>
    </div>
  </header>

  <!-- SIDENAV -->
  <nav class="sidenav">
    <button class="nav-btn active" data-tip="Thư viện" onclick="setNav(this)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
    </button>
    <button class="nav-btn" data-tip="Yêu thích" onclick="setNav(this);showTab('likes')">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    </button>
    <div class="nav-sep"></div>
    <button class="nav-btn" data-tip="Upload" onclick="openModal()">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    </button>
  </nav>

  <!-- LEFT PANEL -->
  <aside class="panel left-panel">
    <div class="panel-header">
      <h3>Danh sách phát</h3>
      <span id="songCount" style="font-size:11px;color:var(--text2)">0 bài</span>
    </div>
    <div class="panel-tabs">
      <button class="ptab active" id="tabAll" onclick="showTab('all')">Tất cả</button>
      <button class="ptab" id="tabLikes" onclick="showTab('likes')">❤️ Yêu thích</button>
    </div>
    <div class="song-scroll" id="sideList"></div>
  </aside>

  <!-- MAIN PANEL -->
  <main class="panel main-panel">
    <div class="visualizer-bar">
      <div class="viz-canvas-wrap"><canvas id="vizCanvas"></canvas></div>
    </div>
    <div class="main-content" id="mainContent"></div>
    <!-- Lyrics panel -->
    <div class="lyrics-panel" id="lyricsPanel">
      <div class="lp-head">
        <h4>🎤 Lời bài hát</h4>
        <button class="lp-close" onclick="toggleLyrics()">✕</button>
      </div>
      <div class="lp-body">
        <div class="lyrics-text" id="lyricsText"><em>Chọn bài hát để xem lời...</em></div>
        <textarea class="lyrics-edit" id="lyricsEdit" rows="12" placeholder="Nhập lời bài hát vào đây...&#10;&#10;Mỗi dòng là một câu hát."></textarea>
      </div>
      <button class="lp-edit-toggle" id="lyricsEditToggle" onclick="toggleLyricsEdit()">✏️ Chỉnh sửa lời</button>
      <button class="lp-save" id="lyricsSave" onclick="saveLyrics()">💾 Lưu lời bài hát</button>
    </div>
  </main>

  <!-- PLAYER BAR -->
  <footer class="player-bar" id="playerBar">
    <!-- Info -->
    <div class="pb-info">
      <div class="pb-cover" id="pbCover">🎵<div class="vinyl"></div></div>
      <div class="pb-text">
        <div class="pb-title" id="pbTitle">Chưa có bài nào</div>
        <div class="pb-artist" id="pbArtist">—</div>
      </div>
      <button class="pb-like" id="pbLike" onclick="toggleLikeCurrent()" title="Yêu thích">♡</button>
    </div>
    <!-- Controls -->
    <div class="pb-controls">
      <div class="ctrl-row">
        <button class="ctrl-btn" id="btnShuffle" onclick="toggleShuffle()" title="Shuffle">
          <svg viewBox="0 0 24 24"><path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
        </button>
        <button class="ctrl-btn" onclick="prevSong()" title="Bài trước">
          <svg viewBox="0 0 24 24"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <button class="play-btn" id="playBtn" onclick="togglePlay()" title="Play/Pause">
          <svg id="playIcon" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </button>
        <button class="ctrl-btn" onclick="nextSong()" title="Bài kế">
          <svg viewBox="0 0 24 24"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <button class="ctrl-btn" id="btnRepeat" onclick="cycleRepeat()" title="Lặp lại">
          <svg viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3" fill="none" stroke="currentColor" stroke-width="2"/></svg>
        </button>
      </div>
      <div class="progress-wrap">
        <span class="time-label" id="curTime">0:00</span>
        <div class="progress-track" id="progressTrack" onclick="seekTo(event)">
          <div class="progress-fill" id="progressFill" style="width:0%"></div>
        </div>
        <span class="time-label" id="totTime">0:00</span>
      </div>
    </div>
    <!-- Extra -->
    <div class="pb-extra">
      <!-- LED VU METER -->
      <div class="vu-meter" id="vuMeter"></div>
      <button class="lyrics-btn" id="lyricsToggleBtn" onclick="toggleLyrics()" title="Lời bài hát">🎤</button>
      <button class="eq-btn" id="eqToggleBtn" onclick="toggleEQ()" title="Equalizer">🎚️</button>
      <div class="vol-wrap">
        <span class="vol-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/></svg>
        </span>
        <input type="range" id="volSlider" min="0" max="100" value="80" oninput="setVol(this.value)">
        <span class="vol-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
        </span>
      </div>
    </div>
  </footer>
</div>

<!-- EQ PANEL -->
<div class="eq-panel" id="eqPanel">
  <div class="eq-head">
    <h4>🎚️ Equalizer</h4>
    <button class="eq-reset" onclick="resetEQ()">Reset</button>
    <button class="eq-close2" onclick="toggleEQ()">✕</button>
  </div>
  <div class="eq-bands" id="eqBands"></div>
  <div class="eq-presets">
    <button class="eq-preset" onclick="applyPreset('flat')">Flat</button>
    <button class="eq-preset" onclick="applyPreset('bass')">Bass Boost</button>
    <button class="eq-preset" onclick="applyPreset('vocal')">Vocal</button>
    <button class="eq-preset" onclick="applyPreset('treble')">Treble</button>
    <button class="eq-preset" onclick="applyPreset('lofi')">Lo-Fi</button>
    <button class="eq-preset" onclick="applyPreset('pop')">Pop</button>
  </div>
</div>

<!-- UPLOAD MODAL -->
<div class="modal-overlay" id="uploadModal">
  <div class="modal-box">
    <h3>✦ Thêm bài hát mới</h3>
    <div class="form-grp">
      <label>Tên bài hát *</label>
      <input type="text" id="iTitle" placeholder="VD: Nơi này có anh" maxlength="200">
    </div>
    <div class="form-grp">
      <label>Nghệ sĩ</label>
      <input type="text" id="iArtist" placeholder="VD: Sơn Tùng M-TP" maxlength="100">
    </div>
    <div class="form-grp">
      <label>Album</label>
      <input type="text" id="iAlbum" placeholder="VD: m-tp M-TP" maxlength="100">
    </div>
    <div class="form-grp">
      <label>File nhạc * (MP3 · WAV · OGG · M4A · FLAC)</label>
      <div class="drop-zone" id="audioDZ" onclick="document.getElementById('audioFile').click()">
        <span class="dz-icon">🎵</span>
        <p id="audioLabel">Kéo thả hoặc nhấn để chọn file</p>
        <input type="file" id="audioFile" accept=".mp3,.wav,.ogg,.m4a,.flac,.aac,audio/*" onchange="onAudioPick(this)">
      </div>
    </div>
    <div class="form-grp">
      <label>Ảnh bìa (tuỳ chọn)</label>
      <div class="drop-zone" id="coverDZ" onclick="document.getElementById('coverFile').click()">
        <span class="dz-icon">🖼️</span>
        <p id="coverLabel">Kéo thả hoặc nhấn để chọn ảnh</p>
        <input type="file" id="coverFile" accept="image/*" onchange="onCoverPick(this)">
      </div>
    </div>
    <div class="upload-bar" id="uploadBar"><div class="fill"></div></div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Hủy</button>
      <button class="btn-go" id="submitBtn" onclick="doUpload()">🚀 Thêm vào thư viện</button>
    </div>
  </div>
</div>

<!-- CONTEXT MENU -->
<div class="ctx-menu" id="ctxMenu">
  <div class="ctx-item" onclick="ctxPlay()">▶️ Phát ngay</div>
  <div class="ctx-item" onclick="ctxLike()"><span id="ctxLikeLabel">♡ Thêm yêu thích</span></div>
  <div class="ctx-sep"></div>
  <div class="ctx-item" onclick="openLyricsFor()">🎤 Xem / Thêm lời</div>
  <div class="ctx-sep"></div>
  <div class="ctx-item danger" onclick="ctxDelete()">🗑️ Xóa bài hát</div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
// ════════════════════════════════════════
// DATA & STATE
// ════════════════════════════════════════
let songs = <?= $songsJson ?>;
let filteredSongs = [...songs];
let currentIdx = -1;
let isPlaying  = false;
let isShuffle  = false;
let repeatMode = 0; // 0=off 1=all 2=one
let currentView = 'grid';
let currentTab  = 'all';
let ctxTargetId = null;

// ── Audio engine ──
const audio = new Audio();
audio.volume = 0.8;
let audioCtx, analyser, source, eqFilters = [];
const EQ_FREQS = [60, 170, 310, 600, 1000, 3000, 6000, 12000, 14000, 16000];

function initAudioContext() {
  if (audioCtx) return;
  audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  analyser = audioCtx.createAnalyser();
  analyser.fftSize = 256;
  source = audioCtx.createMediaElementSource(audio);
  // Build EQ chain
  eqFilters = EQ_FREQS.map((freq, i) => {
    const f = audioCtx.createBiquadFilter();
    f.type = (i === 0) ? 'lowshelf' : (i === EQ_FREQS.length - 1) ? 'highshelf' : 'peaking';
    f.frequency.value = freq;
    f.gain.value = 0;
    return f;
  });
  // Connect: source → eq chain → analyser → destination
  let prev = source;
  eqFilters.forEach(f => { prev.connect(f); prev = f; });
  prev.connect(analyser);
  analyser.connect(audioCtx.destination);
  buildEQUI();
  buildVUMeter();
  drawViz();
  drawVU();
}

// ── Visualizer ──
// ── LED VU Meter (dot column style) ──
const VU_COLS   = 14;  // số cột (kênh tần số)
const VU_DOTS   = 7;   // số chấm mỗi cột (từ dưới lên)
// màu theo hàng (index 0 = dưới cùng)
const VU_COLORS = ['on-green','on-green','on-green','on-yellow','on-yellow','on-orange','on-red'];
let vuPeakDot  = new Array(VU_COLS).fill(0);
let vuPeakHold = new Array(VU_COLS).fill(0);

function buildVUMeter() {
  const vm = document.getElementById('vuMeter');
  vm.querySelectorAll('.vu-col').forEach(el=>el.remove());
  for (let c = 0; c < VU_COLS; c++) {
    const col = document.createElement('div');
    col.className = 'vu-col';
    col.id = 'vuCol' + c;
    // tạo chấm từ trên xuống (index 0 = top = mức cao nhất)
    for (let d = VU_DOTS - 1; d >= 0; d--) {
      const dot = document.createElement('div');
      dot.className = 'vu-dot';
      dot.id = `vuDot${c}_${d}`;
      col.appendChild(dot);
    }
    vm.appendChild(col);
  }
}

function drawVU() {
  if (!analyser) return requestAnimationFrame(drawVU);
  const data = new Uint8Array(analyser.frequencyBinCount);
  analyser.getByteFrequencyData(data);
  for (let c = 0; c < VU_COLS; c++) {
    const idx = Math.floor(Math.pow(c / VU_COLS, 0.65) * (data.length * 0.55));
    const v   = data[idx] / 255;
    const lit = Math.round(v * VU_DOTS); // số chấm sáng từ dưới lên
    // peak hold
    if (lit > vuPeakDot[c]) { vuPeakDot[c] = lit; vuPeakHold[c] = 35; }
    else if (vuPeakHold[c] > 0) vuPeakHold[c]--;
    else vuPeakDot[c] = Math.max(0, vuPeakDot[c] - 1);
    for (let d = 0; d < VU_DOTS; d++) {
      const el = document.getElementById(`vuDot${c}_${d}`);
      if (!el) continue;
      if (d < lit) {
        // chấm sáng — màu theo hàng
        el.className = 'vu-dot ' + VU_COLORS[d];
      } else if (d === vuPeakDot[c] && vuPeakDot[c] > 0) {
        // chấm peak sáng nhạt hơn
        el.className = 'vu-dot ' + VU_COLORS[Math.min(d, VU_COLORS.length-1)];
        el.style.opacity = '0.5';
      } else {
        el.className = 'vu-dot';
        el.style.opacity = '';
      }
    }
  }
  requestAnimationFrame(drawVU);
}

function drawViz() {
  if (!analyser) return;
  const canvas = document.getElementById('vizCanvas');
  const ctx = canvas.getContext('2d');
  canvas.width = canvas.offsetWidth * devicePixelRatio;
  canvas.height = 60 * devicePixelRatio;
  const W = canvas.width, H = canvas.height;
  const data = new Uint8Array(analyser.frequencyBinCount);
  const colors = ['#c084fc','#a855f7','#7c3aed','#22d3ee','#f472b6'];
  function draw() {
    requestAnimationFrame(draw);
    analyser.getByteFrequencyData(data);
    ctx.clearRect(0,0,W,H);
    const bars = 64;
    const bw = W/bars - 1;
    for (let i=0;i<bars;i++) {
      const v = data[Math.floor(i*data.length/bars)] / 255;
      const h = v * H;
      const c = colors[Math.floor(i/bars*colors.length)];
      ctx.fillStyle = c;
      ctx.globalAlpha = 0.7 + v*0.3;
      ctx.beginPath();
      ctx.roundRect(i*(bw+1),H-h,bw,h,2);
      ctx.fill();
    }
  }
  draw();
}

// ════════════════════════════════════════
// RENDER
// ════════════════════════════════════════
function render() {
  const list = currentTab === 'likes' ? filteredSongs.filter(s=>s.likes) : filteredSongs;
  renderSidebar(list);
  renderMain(list);
  document.getElementById('songCount').textContent = songs.length + ' bài';
}

function renderSidebar(list) {
  const el = document.getElementById('sideList');
  if (!list.length) {
    el.innerHTML = `<div style="text-align:center;padding:32px 16px;color:var(--text2);font-size:13px">${currentTab==='likes'?'Chưa có bài yêu thích':'Chưa có bài hát'}</div>`;
    return;
  }
  el.innerHTML = list.map((s,i)=>{
    const ci = songs.indexOf(s);
    const isActive = ci === currentIdx;
    const isPaused = isActive && !isPlaying;
    return `<div class="song-item${isActive?' active':''}${isPaused?' paused':''}"
      data-id="${s.id}" data-ci="${ci}"
      onclick="playSongAt(${ci})"
      oncontextmenu="showCtx(event,'${s.id}')">
      <div class="snum">${i+1}</div>
      <div class="playing-anim"><div class="bar-anim"></div><div class="bar-anim"></div><div class="bar-anim"></div></div>
      <div class="scover">${s.cover?`<img src="${s.cover}" onerror="this.style.display='none'">`:'🎵'}</div>
      <div class="sinfo">
        <div class="stitle">${esc(s.title)}</div>
        <div class="sartist">${esc(s.artist)}</div>
      </div>
      <div class="smeta${s.likes?' liked':''}">
        <span class="sdur">${s.duration||'—'}</span>
        <span class="like-dot"></span>
      </div>
      <div class="item-actions">
        <button class="ia-btn heart${s.likes?' liked':''}" onclick="event.stopPropagation();toggleLike('${s.id}')" title="Yêu thích">${s.likes?'♥':'♡'}</button>
        <button class="ia-btn" onclick="event.stopPropagation();deleteSong('${s.id}')" title="Xóa">🗑</button>
      </div>
    </div>`;
  }).join('');
}

function renderMain(list) {
  const el = document.getElementById('mainContent');
  if (!list.length) {
    el.innerHTML = `<div class="empty-state"><div class="big-ico">🎶</div><h3>Thư viện trống</h3><p>Nhấn <strong>Upload nhạc</strong> ở trên<br>để thêm bài hát đầu tiên!</p></div>`;
    return;
  }
  if (currentView === 'grid') {
    el.innerHTML = `<div class="view-grid">${list.map(s=>{
      const ci = songs.indexOf(s);
      const isActive = ci===currentIdx;
      return `<div class="grid-card${isActive?' active':''}" onclick="playSongAt(${ci})" oncontextmenu="showCtx(event,'${s.id}')">
        <div class="gc-cover">${s.cover?`<img src="${s.cover}" onerror="this.style.display='none'">`:'🎵'}</div>
        <div class="gc-play"><svg width="14" height="14" viewBox="0 0 24 24" fill="#000"><polygon points="5 3 19 12 5 21"/></svg></div>
        <div class="gc-info">
          <div class="gc-title">${esc(s.title)}</div>
          <div class="gc-artist">${esc(s.artist)}</div>
        </div>
      </div>`;
    }).join('')}</div>`;
  } else {
    el.innerHTML = `<div class="view-list">
      <div style="display:flex;align-items:center;gap:14px;padding:6px 14px;font-size:11px;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--border);margin-bottom:4px">
        <span style="width:24px">#</span><span style="width:42px"></span>
        <span style="flex:1">Bài hát</span>
        <span style="width:70px;text-align:right">Lượt nghe</span>
        <span style="width:44px;text-align:right">Thời gian</span>
        <span style="width:64px"></span>
      </div>
      ${list.map((s,i)=>{
        const ci=songs.indexOf(s);
        const isActive=ci===currentIdx;
        return `<div class="list-row${isActive?' active':''}" onclick="playSongAt(${ci})" oncontextmenu="showCtx(event,'${s.id}')">
          <div class="lnum">${i+1}</div>
          <div class="lcover">${s.cover?`<img src="${s.cover}" onerror="this.style.display='none'">`:'🎵'}</div>
          <div class="linfo"><div class="ltitle">${esc(s.title)}</div><div class="lartist">${esc(s.artist)}</div></div>
          <div class="lplays">${fmtNum(s.plays)}</div>
          <div class="ldur">${s.duration||'—'}</div>
          <div class="lactions">
            <button class="lact-btn heart${s.likes?' liked':''}" onclick="event.stopPropagation();toggleLike('${s.id}')" title="Yêu thích">${s.likes?'♥':'♡'}</button>
            <button class="lact-btn" onclick="event.stopPropagation();openLyricsForId('${s.id}')" title="Lời bài hát">🎤</button>
            <button class="lact-btn" onclick="event.stopPropagation();deleteSong('${s.id}')" title="Xóa">🗑</button>
          </div>
        </div>`;
      }).join('')}
    </div>`;
  }
}

// ════════════════════════════════════════
// PLAYBACK
// ════════════════════════════════════════
function playSongAt(idx) {
  if (idx < 0 || idx >= songs.length) return;
  currentIdx = idx;
  const s = songs[idx];
  audio.src = s.audio;
  audio.load();
  audio.play().then(()=>{
    isPlaying = true;
    document.getElementById('playerBar').classList.add('playing');
    updatePlayerUI(s);
    initAudioContext();
    if (audioCtx.state === 'suspended') audioCtx.resume();
  }).catch(e=>{
    showToast('Không thể phát file này','err');
    console.error(e);
  });
  fetch('api.php?action=play&id='+encodeURIComponent(s.id)).catch(()=>{});
  s.plays = (s.plays||0)+1;
  render();
}

function updatePlayerUI(s) {
  document.getElementById('pbTitle').textContent  = s.title;
  document.getElementById('pbArtist').textContent = s.artist;
  const pbLike = document.getElementById('pbLike');
  pbLike.textContent = s.likes ? '♥' : '♡';
  pbLike.className = 'pb-like' + (s.likes?' liked':'');
  const cover = document.getElementById('pbCover');
  cover.innerHTML = s.cover ? `<img src="${s.cover}" onerror="this.outerHTML='🎵'" style="width:100%;height:100%;object-fit:cover"><div class="vinyl"></div>` : `🎵<div class="vinyl"></div>`;
  // Update play icon
  document.getElementById('playIcon').innerHTML = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
  // Lyrics
  document.getElementById('lyricsText').innerHTML = s.lyrics ? s.lyrics.replace(/\n/g,'<br>') : '<em>Chưa có lời. Nhấn "Chỉnh sửa lời" để thêm.</em>';
  document.getElementById('lyricsEdit').value = s.lyrics || '';
}

function togglePlay() {
  if (currentIdx < 0) { playSongAt(0); return; }
  if (audio.paused) {
    audio.play().then(()=>{
      isPlaying=true;
      document.getElementById('playerBar').classList.add('playing');
      document.getElementById('playIcon').innerHTML='<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
      if(audioCtx) audioCtx.resume();
    });
  } else {
    audio.pause();
    isPlaying=false;
    document.getElementById('playerBar').classList.remove('playing');
    document.getElementById('playIcon').innerHTML='<polygon points="5 3 19 12 5 21 5 3"/>';
  }
  render();
}

function prevSong() {
  if (currentIdx <= 0) return;
  playSongAt(currentIdx-1);
}
function nextSong() {
  if (isShuffle) {
    let r; do { r=Math.floor(Math.random()*songs.length); } while(r===currentIdx&&songs.length>1);
    playSongAt(r); return;
  }
  if (repeatMode===2) { audio.currentTime=0; audio.play(); return; }
  if (currentIdx < songs.length-1) playSongAt(currentIdx+1);
  else if (repeatMode===1) playSongAt(0);
}

audio.addEventListener('timeupdate', ()=>{
  if (!audio.duration||isNaN(audio.duration)) return;
  const p = audio.currentTime/audio.duration*100;
  document.getElementById('progressFill').style.width = p+'%';
  document.getElementById('progressTrack').style.setProperty('--p', p+'%');
  document.getElementById('curTime').textContent = fmt(audio.currentTime);
  document.getElementById('totTime').textContent = fmt(audio.duration);
});
audio.addEventListener('loadedmetadata', ()=>{
  const dur = fmt(audio.duration);
  document.getElementById('totTime').textContent = dur;
  if (currentIdx>=0) {
    songs[currentIdx].duration = dur;
    render();
    fetch('api.php?action=duration&id='+encodeURIComponent(songs[currentIdx].id), {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'duration='+encodeURIComponent(dur)}).catch(()=>{});
  }
});
audio.addEventListener('ended', nextSong);
audio.addEventListener('error', ()=>{
  showToast('Lỗi: File không hợp lệ hoặc không tìm thấy','err');
  isPlaying=false; render();
});

function seekTo(e) {
  if (!audio.duration||isNaN(audio.duration)) return;
  const r = e.currentTarget.getBoundingClientRect();
  audio.currentTime = (e.clientX-r.left)/r.width*audio.duration;
}
function setVol(v) { audio.volume=v/100; }
function toggleShuffle() {
  isShuffle=!isShuffle;
  document.getElementById('btnShuffle').classList.toggle('active',isShuffle);
  showToast(isShuffle?'Bật shuffle 🔀':'Tắt shuffle','');
}
function cycleRepeat() {
  repeatMode=(repeatMode+1)%3;
  const btn=document.getElementById('btnRepeat');
  btn.classList.toggle('active',repeatMode>0);
  btn.title=['Lặp lại: Tắt','Lặp tất cả','Lặp 1 bài'][repeatMode];
  showToast(['Tắt lặp lại','Lặp tất cả 🔁','Lặp 1 bài 🔂'][repeatMode],'');
}

// ════════════════════════════════════════
// LIKE
// ════════════════════════════════════════
function toggleLike(id) {
  const s = songs.find(x=>x.id===id);
  if(!s)return;
  s.likes=!s.likes;
  fetch('api.php?action=like&id='+encodeURIComponent(id)).catch(()=>{});
  if(currentIdx>=0&&songs[currentIdx].id===id){
    const btn=document.getElementById('pbLike');
    btn.textContent=s.likes?'♥':'♡';
    btn.className='pb-like'+(s.likes?' liked':'');
  }
  showToast(s.likes?'Đã thêm vào yêu thích ♥':'Đã bỏ yêu thích','');
  render();
}
function toggleLikeCurrent() {
  if(currentIdx<0)return;
  toggleLike(songs[currentIdx].id);
}

// ════════════════════════════════════════
// DELETE
// ════════════════════════════════════════
async function deleteSong(id) {
  if(!confirm('Xóa bài hát này?'))return;
  const res=await fetch('delete.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+encodeURIComponent(id)});
  const d=await res.json();
  if(d.success){
    const idx=songs.findIndex(s=>s.id===id);
    if(idx===currentIdx){audio.pause();audio.src='';isPlaying=false;currentIdx=-1;document.getElementById('playerBar').classList.remove('playing');document.getElementById('pbTitle').textContent='Chưa có bài nào';document.getElementById('pbArtist').textContent='—';document.getElementById('pbCover').innerHTML='🎵<div class="vinyl"></div>';}
    else if(idx<currentIdx) currentIdx--;
    songs.splice(idx,1);
    filteredSongs=filterArr(document.getElementById('searchInput').value);
    render();
    showToast('Đã xóa bài hát','ok');
  } else showToast('Không thể xóa: '+(d.message||''),'err');
}

// ════════════════════════════════════════
// SEARCH & FILTER
// ════════════════════════════════════════
function filterSongs(q) {
  filteredSongs=filterArr(q);
  render();
}
function filterArr(q) {
  if(!q||!q.trim()) return [...songs];
  const lq=q.toLowerCase();
  return songs.filter(s=>s.title.toLowerCase().includes(lq)||s.artist.toLowerCase().includes(lq)||(s.album||'').toLowerCase().includes(lq));
}
function showTab(t) {
  currentTab=t;
  document.getElementById('tabAll').classList.toggle('active',t==='all');
  document.getElementById('tabLikes').classList.toggle('active',t==='likes');
  render();
}
function setView(v) {
  currentView=v;
  document.getElementById('btnGrid').classList.toggle('active',v==='grid');
  document.getElementById('btnList').classList.toggle('active',v==='list');
  render();
}
function setNav(btn) {
  document.querySelectorAll('.nav-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
}

// ════════════════════════════════════════
// LYRICS
// ════════════════════════════════════════
let lyricsEditing=false;
function toggleLyrics() {
  const p=document.getElementById('lyricsPanel');
  const isOpen=p.classList.toggle('open');
  document.getElementById('lyricsToggleBtn').classList.toggle('active',isOpen);
  if(isOpen&&currentIdx>=0) updateLyricsPanel(songs[currentIdx]);
}
function updateLyricsPanel(s) {
  document.getElementById('lyricsText').innerHTML = s.lyrics ? s.lyrics.replace(/\n/g,'<br>') : '<em>Chưa có lời. Nhấn "Chỉnh sửa lời" để thêm.</em>';
  document.getElementById('lyricsEdit').value = s.lyrics||'';
}
function toggleLyricsEdit() {
  lyricsEditing=!lyricsEditing;
  document.getElementById('lyricsText').style.display=lyricsEditing?'none':'';
  document.getElementById('lyricsEdit').style.display=lyricsEditing?'block':'none';
  document.getElementById('lyricsSave').style.display=lyricsEditing?'block':'none';
  document.getElementById('lyricsEditToggle').textContent=lyricsEditing?'↩ Hủy chỉnh sửa':'✏️ Chỉnh sửa lời';
}
async function saveLyrics() {
  if(currentIdx<0)return;
  const s=songs[currentIdx];
  const lyrics=document.getElementById('lyricsEdit').value.trim();
  s.lyrics=lyrics;
  await fetch('api.php?action=lyrics&id='+encodeURIComponent(s.id),{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'lyrics='+encodeURIComponent(lyrics)}).catch(()=>{});
  document.getElementById('lyricsText').innerHTML=lyrics?lyrics.replace(/\n/g,'<br>'):'<em>Chưa có lời.</em>';
  toggleLyricsEdit();
  showToast('Đã lưu lời bài hát 🎤','ok');
}
function openLyricsFor() {
  if(ctxTargetId) { const i=songs.findIndex(s=>s.id===ctxTargetId); if(i>=0) currentIdx=i; }
  if(!document.getElementById('lyricsPanel').classList.contains('open')) toggleLyrics();
  else if(currentIdx>=0) updateLyricsPanel(songs[currentIdx]);
  closeCtx();
}
function openLyricsForId(id) {
  ctxTargetId=id; openLyricsFor();
}

// ════════════════════════════════════════
// EQUALIZER
// ════════════════════════════════════════
function buildEQUI() {
  const el=document.getElementById('eqBands');
  const labels=['60Hz','170Hz','310Hz','600Hz','1kHz','3kHz','6kHz','12kHz','14kHz','16kHz'];
  el.innerHTML=eqFilters.map((f,i)=>`
    <div class="eq-band">
      <label>${labels[i]}</label>
      <input type="range" min="-12" max="12" value="0" step="1"
        oninput="setEQBand(${i},this.value)"
        id="eqSlider${i}">
    </div>`).join('');
}
function setEQBand(i,v) { if(eqFilters[i]) eqFilters[i].gain.value=parseFloat(v); }
function resetEQ() {
  eqFilters.forEach((f,i)=>{ f.gain.value=0; const el=document.getElementById('eqSlider'+i); if(el)el.value=0; });
  document.querySelectorAll('.eq-preset').forEach(b=>b.classList.remove('active'));
}
const EQ_PRESETS={
  flat:[0,0,0,0,0,0,0,0,0,0],
  bass:[8,7,6,3,0,0,0,0,0,0],
  vocal:[-2,-2,0,4,6,4,2,0,-2,-2],
  treble:[0,0,0,0,0,2,4,6,7,8],
  lofi:[4,3,2,-2,-4,-6,-6,-6,-6,-6],
  pop:[-2,0,2,4,4,2,0,-1,-1,-1]
};
function applyPreset(name) {
  if(!audioCtx) initAudioContext();
  const vals=EQ_PRESETS[name]||EQ_PRESETS.flat;
  vals.forEach((v,i)=>{ setEQBand(i,v); const el=document.getElementById('eqSlider'+i); if(el)el.value=v; });
  document.querySelectorAll('.eq-preset').forEach(b=>b.classList.toggle('active',b.textContent.toLowerCase().includes(name)||b.getAttribute('onclick').includes("'"+name+"'")));
}
function toggleEQ() {
  const p=document.getElementById('eqPanel');
  const isOpen=p.classList.toggle('open');
  document.getElementById('eqToggleBtn').classList.toggle('active',isOpen);
  if(isOpen&&!audioCtx) initAudioContext();
}

// ════════════════════════════════════════
// CONTEXT MENU
// ════════════════════════════════════════
function showCtx(e,id) {
  e.preventDefault();
  ctxTargetId=id;
  const s=songs.find(x=>x.id===id);
  if(s) document.getElementById('ctxLikeLabel').textContent=s.likes?'♥ Bỏ yêu thích':'♡ Thêm yêu thích';
  const m=document.getElementById('ctxMenu');
  m.style.left=Math.min(e.clientX,window.innerWidth-180)+'px';
  m.style.top=Math.min(e.clientY,window.innerHeight-200)+'px';
  m.classList.add('open');
}
function closeCtx() { document.getElementById('ctxMenu').classList.remove('open'); }
function ctxPlay() { const i=songs.findIndex(s=>s.id===ctxTargetId); if(i>=0)playSongAt(i); closeCtx(); }
function ctxLike() { if(ctxTargetId)toggleLike(ctxTargetId); closeCtx(); }
function ctxDelete() { if(ctxTargetId)deleteSong(ctxTargetId); closeCtx(); }
document.addEventListener('click', closeCtx);

// ════════════════════════════════════════
// UPLOAD
// ════════════════════════════════════════
function openModal() { document.getElementById('uploadModal').classList.add('open'); }
function closeModal() {
  document.getElementById('uploadModal').classList.remove('open');
  ['iTitle','iArtist','iAlbum'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('audioLabel').textContent='Kéo thả hoặc nhấn để chọn file';
  document.getElementById('coverLabel').textContent='Kéo thả hoặc nhấn để chọn ảnh';
  document.getElementById('audioDZ').classList.remove('has-file');
  document.getElementById('coverDZ').classList.remove('has-file');
  document.getElementById('audioFile').value='';
  document.getElementById('coverFile').value='';
  document.getElementById('uploadBar').style.display='none';
  document.getElementById('submitBtn').disabled=false;
}
function onAudioPick(inp) {
  if(!inp.files[0])return;
  document.getElementById('audioLabel').textContent='✅ '+inp.files[0].name;
  document.getElementById('audioDZ').classList.add('has-file');
  if(!document.getElementById('iTitle').value.trim())
    document.getElementById('iTitle').value=inp.files[0].name.replace(/\.[^.]+$/,'').replace(/[_-]/g,' ');
}
function onCoverPick(inp) {
  if(!inp.files[0])return;
  document.getElementById('coverLabel').textContent='✅ '+inp.files[0].name;
  document.getElementById('coverDZ').classList.add('has-file');
}
async function doUpload() {
  const title=document.getElementById('iTitle').value.trim();
  const af=document.getElementById('audioFile').files[0];
  if(!title){showToast('Nhập tên bài hát!','err');return;}
  if(!af){showToast('Chọn file nhạc!','err');return;}
  document.getElementById('submitBtn').disabled=true;
  document.getElementById('uploadBar').style.display='block';
  const fd=new FormData();
  fd.append('title',title);
  fd.append('artist',document.getElementById('iArtist').value.trim());
  fd.append('album',document.getElementById('iAlbum').value.trim());
  fd.append('audio',af);
  const cf=document.getElementById('coverFile').files[0];
  if(cf)fd.append('cover',cf);
  try {
    const res=await fetch('upload.php',{method:'POST',body:fd});
    const d=await res.json();
    if(d.success){
      showToast('🎉 Upload thành công!','ok');
      closeModal();
      setTimeout(()=>location.reload(),800);
    } else {
      showToast('Lỗi: '+(d.message||'Upload thất bại'),'err');
      document.getElementById('submitBtn').disabled=false;
      document.getElementById('uploadBar').style.display='none';
    }
  } catch(e){
    showToast('Lỗi kết nối: '+e.message,'err');
    document.getElementById('submitBtn').disabled=false;
    document.getElementById('uploadBar').style.display='none';
  }
}

// ════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════
function fmt(s) { if(!s||isNaN(s))return'0:00'; const m=Math.floor(s/60),sec=Math.floor(s%60); return m+':'+(sec<10?'0':'')+sec; }
function fmtNum(n) { return n>=1000000?(n/1000000).toFixed(1)+'M':n>=1000?(n/1000).toFixed(1)+'k':n||0; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

let toastTmr;
function showToast(msg,type='') {
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show '+type;
  clearTimeout(toastTmr);
  toastTmr=setTimeout(()=>t.classList.remove('show'),2800);
}

// ── Keyboard shortcuts ──
document.addEventListener('keydown', e=>{
  if(e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA')return;
  if(e.code==='Space'){e.preventDefault();togglePlay();}
  if(e.code==='ArrowRight')nextSong();
  if(e.code==='ArrowLeft')prevSong();
  if(e.code==='KeyL')toggleLike(currentIdx>=0?songs[currentIdx].id:'');
  if(e.code==='KeyS')toggleShuffle();
});

// Close modal on backdrop click
document.getElementById('uploadModal').addEventListener('click',e=>{if(e.target===document.getElementById('uploadModal'))closeModal();});

// ── Init ──
render();
</script>
</body>
</html>