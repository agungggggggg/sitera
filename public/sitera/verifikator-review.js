(() => {
  "use strict";
  const $ = (s,root=document) => root.querySelector(s);
  const esc = value => String(value ?? "").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
  const STORAGE_KEY = "sitera-verifikator-preview-v1";
  const seed = [
    {id:"SIT-VRF-026",group:"P3A Tirta Utara",di:"DI Contoh Utara",province:"Provinsi Ilustrasi A",regency:"Kabupaten Contoh Utara",district:"Kecamatan Utara",village:"Desa Tirta",date:"2026-10-30",version:2,status:"Menunggu pemeriksaan",bast:"BAST_Tirta_Utara_v2.pdf",lpj:"LPJ_Tirta_Utara_v2.pdf",pptx:"Dokumentasi_Tirta_Utara_v2.pptx",sizeBast:"1,8 MB",sizeLpj:"3,2 MB",sizePptx:"18,4 MB",cpcl:{no:"001",kewenangan:"Kabupaten",keterangan:"Data ilustrasi",ketua:"Budi Santoso",realisasi:"1.840",luas:"30,5",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}},
    {id:"SIT-VRF-025",group:"Poktan Contoh Barat",di:"DI Contoh Barat",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Barat",district:"Kecamatan Barat",village:"Desa Simulasi",date:"2026-10-29",version:1,status:"Menunggu pemeriksaan",bast:"BAST_Contoh_Barat.pdf",lpj:"LPJ_Contoh_Barat.pdf",pptx:"Dokumentasi_Contoh_Barat.pptx",sizeBast:"2,1 MB",sizeLpj:"4,0 MB",sizePptx:"22,6 MB",cpcl:{no:"002",kewenangan:"Kabupaten",keterangan:"Data ilustrasi",ketua:"Siti Rahma",realisasi:"920",luas:"18,0",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}},
    {id:"SIT-VRF-024",group:"GP3A Ilustrasi Timur",di:"DI Contoh Timur",province:"Provinsi Ilustrasi C",regency:"Kabupaten Contoh Timur",district:"Kecamatan Timur",village:"Desa Rekaan",date:"2026-10-28",version:1,status:"Menunggu pemeriksaan",bast:"BAST_Ilustrasi_Timur.pdf",lpj:"LPJ_Ilustrasi_Timur.pdf",pptx:"Dokumentasi_Ilustrasi_Timur.pptx",sizeBast:"1,5 MB",sizeLpj:"2,8 MB",sizePptx:"12,2 MB",cpcl:{no:"003",kewenangan:"Kabupaten",keterangan:"Data ilustrasi",ketua:"Agus Pratama",realisasi:"1.120",luas:"22,4",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}},
    {id:"SIT-VRF-023",group:"P3A Karya Bersama",di:"DI Karya",province:"Provinsi Ilustrasi A",regency:"Kabupaten Contoh Selatan",district:"Kecamatan Selatan",village:"Desa Karya",date:"2026-10-27",version:1,status:"Tidak sesuai",bast:"BAST_Karya.pdf",lpj:"LPJ_Karya.pdf",pptx:"Dokumentasi_Karya.pptx",sizeBast:"1,2 MB",sizeLpj:"2,5 MB",sizePptx:"16,0 MB",reviewer:"Verifikator Demo",reviewedAt:"29 Okt 2026 14:20",note:"Mohon cocokkan nilai dan identitas kelompok pada BAST dengan data CPCL. Periksa kembali kelengkapan kuitansi yang dirujuk pada LPJ.",cpcl:{no:"004",kewenangan:"Kabupaten",keterangan:"Data ilustrasi",ketua:"Dewi Lestari",realisasi:"760",luas:"15,2",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}},
    {id:"SIT-VRF-022",group:"Poktan Sumber Makmur",di:"DI Sumber",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Barat",district:"Kecamatan Sumber",village:"Desa Makmur",date:"2026-10-26",version:1,status:"Sesuai",bast:"BAST_Sumber_Makmur.pdf",lpj:"LPJ_Sumber_Makmur.pdf",pptx:"Dokumentasi_Sumber_Makmur.pptx",sizeBast:"1,7 MB",sizeLpj:"3,6 MB",sizePptx:"19,3 MB",reviewer:"Verifikator Demo",reviewedAt:"28 Okt 2026 10:05",note:"",cpcl:{no:"005",kewenangan:"Kabupaten",keterangan:"Data ilustrasi",ketua:"Rudi Hartono",realisasi:"1.430",luas:"27,0",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}},
    {id:"SIT-VRF-021",group:"P3A Mekar Jaya",di:"DI Mekar",province:"Provinsi Ilustrasi C",regency:"Kabupaten Contoh Timur",district:"Kecamatan Mekar",village:"Desa Jaya",date:"2026-10-25",version:2,status:"Tidak sesuai",bast:"BAST_Mekar_v2.pdf",lpj:"LPJ_Mekar_v2.pdf",pptx:"Dokumentasi_Mekar_v2.pptx",sizeBast:"1,9 MB",sizeLpj:"3,1 MB",sizePptx:"20,1 MB",reviewer:"Verifikator Demo",reviewedAt:"27 Okt 2026 16:40",note:"Lampiran LPJ belum menunjukkan keterkaitan kuitansi dengan kegiatan yang dilaporkan. Mohon lengkapi dan kirim ulang.",cpcl:{no:"006",kewenangan:"Kabupaten",keterangan:"Data ilustrasi",ketua:"Nina Puspita",realisasi:"1.010",luas:"20,1",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}}
  ];
  let records;
  try { const saved=JSON.parse(localStorage.getItem(STORAGE_KEY)||"null"); records=Array.isArray(saved)&&saved.length?saved:seed; } catch (_) { records=seed; }
  let activeId=null, toastTimer=null, activeView="dashboard";
  const save=()=>{try{localStorage.setItem(STORAGE_KEY,JSON.stringify(records));}catch(_){}};
  const toast=message=>{const el=$("#toast");el.textContent=message;el.classList.add("show");clearTimeout(toastTimer);toastTimer=setTimeout(()=>el.classList.remove("show"),3000);};
  const waiting=()=>records.filter(r=>r.status==="Menunggu pemeriksaan");
  const reviewed=()=>records.filter(r=>r.status==="Sesuai"||r.status==="Tidak sesuai");
  const badge=status=>'<span class="status '+(status==="Sesuai"?"done":status==="Tidak sesuai"?"revision":"waiting")+'">'+esc(status)+'</span>';
  const dateLabel=iso=>{const d=new Date(iso+"T00:00:00");return d.toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"});};
  const CPCL_DONUT_DATA={
   "Nasional":{total:1840,sesuai:500,belumSesuai:340,belumMelapor:1000},
   "Kabupaten Contoh Utara":{total:480,sesuai:130,belumSesuai:80,belumMelapor:270},
   "Kabupaten Contoh Barat":{total:510,sesuai:140,belumSesuai:90,belumMelapor:280},
   "Kabupaten Contoh Timur":{total:450,sesuai:120,belumSesuai:80,belumMelapor:250},
   "Kabupaten Contoh Selatan":{total:400,sesuai:110,belumSesuai:90,belumMelapor:200}
  };
  const CPCL_DONUT_COLORS={sesuai:"#0B3B70",belumSesuai:"#4E8CCB",belumMelapor:"#B9D2EA"};
  const formatCount=value=>new Intl.NumberFormat("id-ID").format(value);
  function setView(view){
    activeView=view;
    ["dashboard","queue","history","detail"].forEach(name=>$("#view-"+name).hidden=name!==view);
    document.querySelectorAll(".nav-item").forEach(n=>n.classList.toggle("active",n.dataset.view===view));
    const labels={dashboard:["Ringkasan","Ringkasan pemeriksaan","Periksa laporan dari seluruh wilayah sesuai kewenangan akun Anda."],queue:["Antrean Pemeriksaan","Antrean pemeriksaan","Tinjau satu versi kiriman dan berikan satu hasil gabungan."],history:["Riwayat Pemeriksaan","Riwayat pemeriksaan","Telusuri hasil pemeriksaan dan catatan pada versi yang pernah dinilai."]};
    if(labels[view]){ $("#breadcrumb-current").textContent=labels[view][0];$("#page-title").textContent=labels[view][1];$("#page-description").textContent=labels[view][2];}
    window.scrollTo({top:0,behavior:"smooth"});
    renderAll();
  }
  function metric(label,value,meta,icon,cls=""){return '<article class="metric '+cls+'"><div class="metric-top"><span>'+label+'</span><span class="metric-icon">'+icon+'</span></div><div class="metric-value">'+value+'</div><div class="metric-meta">'+meta+'</div></article>';}
  function renderCpclDonut(){
   const scope=$("#cpcl-chart-regency").value||"Nasional";
   const data=CPCL_DONUT_DATA[scope]||CPCL_DONUT_DATA.Nasional;
   const segments=[
    {key:"sesuai",label:"BAST sudah sesuai",count:data.sesuai,color:CPCL_DONUT_COLORS.sesuai},
    {key:"belumSesuai",label:"BAST belum sesuai",count:data.belumSesuai,color:CPCL_DONUT_COLORS.belumSesuai},
    {key:"belumMelapor",label:"Poktan belum melapor",count:data.belumMelapor,color:CPCL_DONUT_COLORS.belumMelapor}
   ];
   const circumference=2*Math.PI*78;let offset=0;
   const arcs=segments.map(item=>{
    const length=data.total?circumference*(item.count/data.total):0,visible=Math.max(0,length-1.4);
    const circle='<circle cx="110" cy="110" r="78" fill="none" stroke="'+item.color+'" stroke-width="28" stroke-dasharray="'+visible+' '+(circumference-visible)+'" stroke-dashoffset="'+(-offset)+'"></circle>';
    offset+=length;return circle;
   }).join("");
   $("#cpcl-donut-svg").innerHTML='<circle cx="110" cy="110" r="78" fill="none" stroke="#edf3f8" stroke-width="28"></circle><g transform="rotate(-90 110 110)">'+arcs+'</g>';
   $("#cpcl-total").textContent=formatCount(data.total);
   $("#cpcl-scope-label").textContent=scope==="Nasional"?"Seluruh Indonesia":scope;
   $("#cpcl-donut-legend").innerHTML=segments.map(item=>'<div class="cpcl-donut-item"><span class="cpcl-donut-dot" style="background:'+item.color+'"></span><span class="cpcl-donut-name">'+item.label+'</span><strong class="cpcl-donut-value">'+formatCount(item.count)+'</strong></div>').join("");
   $(".cpcl-donut").setAttribute("aria-label","Diagram donat status CPCL untuk "+scope+". Total "+formatCount(data.total)+" CPCL.");
  }
  function renderDashboard(){
    renderCpclDonut();
    const q=waiting(),h=reviewed(),bad=records.filter(r=>r.status==="Tidak sesuai");
    $("#dashboard-metrics").innerHTML=metric("Menunggu pemeriksaan",q.length,"Paket siap ditinjau","▤","amber")+metric("Sesuai",records.filter(r=>r.status==="Sesuai").length,"Hasil pemeriksaan gabungan","✓","green")+metric("Tidak sesuai",bad.length,"Menunggu perbaikan Pelapor","!","red")+metric("Sudah diperiksa",h.length,"Riwayat hasil tersimpan","◷");
    $("#queue-count").textContent=q.length;
    $("#recent-queue").innerHTML=q.slice().sort((a,b)=>a.date.localeCompare(b.date)).slice(0,4).map(r=>'<div class="activity-item"><span class="activity-mark amber">▤</span><span class="activity-copy"><strong>'+esc(r.group)+'</strong><small>'+esc(r.id)+' · '+esc(r.regency)+'</small></span><button class="action-link" data-action="open-detail" data-id="'+esc(r.id)+'">Periksa →</button></div>').join("")||'<div class="empty-state">Tidak ada laporan yang menunggu pemeriksaan.</div>';
    $("#needs-followup").innerHTML=renderTable(bad,"followup");
  }
  function renderTable(rows,mode){
    if(!rows.length)return '<div class="empty-state">Tidak ada data yang sesuai dengan filter.</div>';
    const headers=mode==="queue"?["ID Laporan","Kelompok Tani","Nama DI","Kabupaten/Kota","Tanggal kirim","Versi","Aksi"]:["ID Laporan","Kelompok Tani","Kabupaten/Kota","Tanggal pemeriksaan","Hasil","Catatan","Aksi"];
    const body=rows.map(r=>mode==="queue"?'<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.group)+'</span></td><td>'+esc(r.di)+'</td><td>'+esc(r.regency)+'</td><td>'+dateLabel(r.date)+'</td><td>v'+r.version+'</td><td><button class="queue-row-action" data-action="open-detail" data-id="'+esc(r.id)+'">Periksa</button></td></tr>':'<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.group)+'</span></td><td>'+esc(r.regency)+'</td><td>'+esc(r.reviewedAt||"—")+'</td><td>'+badge(r.status)+'</td><td><span class="cell-sub">'+esc(r.note||"—")+'</span></td><td><button class="action-link" data-action="open-detail" data-id="'+esc(r.id)+'">Detail</button></td></tr>').join("");
    return '<div class="table-wrap"><table class="data-table"><thead><tr>'+headers.map(h=>'<th>'+h+'</th>').join("")+'</tr></thead><tbody>'+body+'</tbody></table></div><div class="table-bottom"><span>Menampilkan '+rows.length+' paket · data simulasi</span><span class="pagination"><button class="page-button active">1</button></span></div>';
  }
  function renderQueue(){
    const q=($("#queue-search").value||"").toLowerCase().trim(),prov=$("#queue-province").value,kab=$("#queue-regency").value,sort=$("#queue-sort").value;
    let rows=waiting().filter(r=>(!q||[r.id,r.group,r.di,r.regency].some(v=>String(v).toLowerCase().includes(q)))&&(!prov||r.province===prov)&&(!kab||r.regency===kab));
    rows.sort((a,b)=>sort==="date-desc"?b.date.localeCompare(a.date):sort==="group-asc"?a.group.localeCompare(b.group,"id"):sort==="group-desc"?b.group.localeCompare(a.group,"id"):a.date.localeCompare(b.date));
    $("#queue-table").innerHTML=renderTable(rows,"queue");
    $("#queue-count").textContent=waiting().length;
  }
  function renderHistory(){
    const q=($("#history-search").value||"").toLowerCase().trim(),result=$("#history-result").value,sort=$("#history-sort").value;
    let rows=reviewed().filter(r=>(!q||[r.id,r.group,r.di,r.regency].some(v=>String(v).toLowerCase().includes(q)))&&(!result||r.status===result));
    rows.sort((a,b)=>sort==="date-asc"?a.date.localeCompare(b.date):b.date.localeCompare(a.date));
    $("#history-table").innerHTML=renderTable(rows,"history");
  }
  function renderDetail(id){
    const r=records.find(x=>x.id===id);if(!r)return;
    activeId=id;
    $("#detail-id").textContent=r.id;
    $("#detail-subtitle").textContent=r.group+" · "+r.di+" · "+r.regency;
    $("#detail-status").innerHTML=badge(r.status);
    $("#detail-version").textContent="Versi "+r.version;
    $("#detail-state").textContent=r.status==="Menunggu pemeriksaan"?"Versi menunggu hasil pemeriksaan": "Hasil tersimpan · "+(r.reviewedAt||"riwayat demo");
    const cpcl=[["No.",r.cpcl.no],["Nama DI",r.di],["Kewenangan",r.cpcl.kewenangan],["Keterangan",r.cpcl.keterangan],["Provinsi",r.province],["Kab_Kota",r.regency],["Kecamatan",r.district],["Desa/Kelurahan",r.village],["Kel_Tani",r.group],["Nama Ketua",r.cpcl.ketua],["Realisasi",r.cpcl.realisasi],["Luas_ha",r.cpcl.luas],["X",r.cpcl.x],["Y",r.cpcl.y]];
    $("#detail-cpcl").innerHTML=cpcl.map(([k,v])=>'<div class="detail-field"><small>'+esc(k)+'</small><strong>'+esc(v||"Belum tersedia")+'</strong></div>').join("");
    const docs=[["BAST","PDF",r.bast,r.sizeBast],["LPJ","PDF",r.lpj,r.sizeLpj],["Dokumentasi","PPTX",r.pptx,r.sizePptx]];
    $("#detail-documents").innerHTML=docs.map(([name,type,file,size])=>'<div class="review-document"><span class="review-document-mark '+(type==="PPTX"?"pptx":"")+'">'+type+'</span><span class="review-document-copy"><strong>'+esc(file||"Belum tersedia")+'</strong><small>'+name+' · '+type+' · '+esc(size||"Ukuran belum dicatat")+'</small></span><span class="review-document-meta">Berkas demo<button data-action="file-info" data-name="'+esc(file||"")+'">Info berkas</button></span></div>').join("");
    const hist=[];
    if(r.reviewedAt)hist.push({title:"Hasil "+r.status,detail:r.note||"Tidak ada catatan pemeriksaan.",date:r.reviewedAt,reviewer:r.reviewer||"Verifikator demo"});
    hist.push({title:"Versi "+r.version+" dikirim",detail:"Paket lengkap masuk antrean pemeriksaan.",date:dateLabel(r.date),reviewer:"Pelapor kabupaten"});
    $("#detail-history").innerHTML=hist.map(h=>'<div class="review-history-row"><span><strong>'+esc(h.title)+'</strong><small>'+esc(h.detail)+' · '+esc(h.reviewer)+'</small></span><time>'+esc(h.date)+'</time></div>').join("");
    const canReview=r.status==="Menunggu pemeriksaan";
    $("#review-note").value="";
    $("#review-note").disabled=!canReview;
    $("#note-required").textContent="Wajib untuk Tidak sesuai";
    $("#review-error").hidden=true;
    document.querySelectorAll('[data-action="submit-review"]').forEach(b=>b.disabled=!canReview);
    setView("detail");
  }
  function renderAll(){
    if(activeView==="dashboard")renderDashboard();
    if(activeView==="queue")renderQueue();
    if(activeView==="history")renderHistory();
  }
  function submitReview(result){
    const r=records.find(x=>x.id===activeId);
    if(!r){toast("Laporan tidak ditemukan.");return;}
    if(r.status!=="Menunggu pemeriksaan"||!r.version){$("#review-error").textContent="Versi ini sudah berubah atau tidak lagi menunggu pemeriksaan. Muat ulang antrean.";$("#review-error").hidden=false;return;}
    const note=$("#review-note").value.trim();
    if(result==="Tidak sesuai"&&!note){$("#review-error").textContent="Catatan wajib diisi untuk hasil Tidak sesuai.";$("#review-error").hidden=false;$("#review-note").focus();return;}
    r.status=result;r.note=result==="Tidak sesuai"?note:"";r.reviewer="Verifikator Demo";r.reviewedAt="30 Okt 2026 · "+new Date().toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"});
    save();toast("Hasil "+result+" tersimpan pada simulasi browser.");setView("history");
  }
  document.addEventListener("click",event=>{
    const view=event.target.closest("[data-view]");if(view){setView(view.dataset.view);return;}
    const b=event.target.closest("[data-action]");if(!b)return;
    const id=b.dataset.id;
    switch(b.dataset.action){
      case "go-queue":setView("queue");break;
      case "go-history":setView("history");break;
      case "open-detail":renderDetail(id);break;
      case "back-queue":setView("queue");break;
      case "reset-queue":["queue-search","queue-province","queue-regency"].forEach(k=>$("#"+k).value="");$("#queue-sort").value="date-asc";renderQueue();break;
      case "submit-review":submitReview(b.dataset.result);break;
      case "file-info":toast("Berkas ini hanya nama file simulasi; isi dokumen belum tersedia untuk pratinjau.");break;
    }
  });
  ["queue-search","queue-province","queue-regency","queue-sort"].forEach(id=>{ $("#"+id).addEventListener("input",renderQueue);$("#"+id).addEventListener("change",renderQueue);});
  ["history-search","history-result","history-sort"].forEach(id=>{ $("#"+id).addEventListener("input",renderHistory);$("#"+id).addEventListener("change",renderHistory);});
  $("#cpcl-chart-regency").addEventListener("change",renderCpclDonut);
  $("#notification-button").addEventListener("click",()=>toast("Notifikasi belum terhubung pada pratinjau UI."));
  renderDashboard();renderQueue();renderHistory();
})();