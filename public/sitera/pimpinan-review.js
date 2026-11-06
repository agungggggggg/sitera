(() => {
  "use strict";
  const $ = (selector, root=document) => root.querySelector(selector);
  const esc = value => String(value ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
  const STORAGE_KEY = "sitera-pimpinan-preview-v1";
  const seed = [
    {id:"SIT-VRF-026",groupId:"GRP-UT-01",group:"P3A Tirta Utara",di:"DI Contoh Utara",province:"Provinsi Ilustrasi A",regency:"Kabupaten Contoh Utara",district:"Kecamatan Utara",village:"Desa Tirta",sentAt:"2026-10-30",updatedAt:"2026-10-30",version:2,status:"Menunggu pemeriksaan",docs:{bast:{name:"BAST_Tirta_Utara_v2.pdf",ready:true},lpj:{name:"LPJ_Tirta_Utara_v2.pdf",ready:true},pptx:{name:"Dokumentasi_Tirta_Utara_v2.pptx",ready:true}}},
    {id:"SIT-VRF-025",groupId:"GRP-BR-01",group:"Poktan Contoh Barat",di:"DI Contoh Barat",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Barat",district:"Kecamatan Barat",village:"Desa Simulasi",sentAt:"2026-10-29",updatedAt:"2026-10-29",version:1,status:"Menunggu pemeriksaan",docs:{bast:{name:"BAST_Contoh_Barat.pdf",ready:true},lpj:{name:"LPJ_Contoh_Barat.pdf",ready:true},pptx:{name:"Dokumentasi_Contoh_Barat.pptx",ready:true}}},
    {id:"SIT-VRF-024",groupId:"GRP-TM-01",group:"GP3A Ilustrasi Timur",di:"DI Contoh Timur",province:"Provinsi Ilustrasi C",regency:"Kabupaten Contoh Timur",district:"Kecamatan Timur",village:"Desa Rekaan",sentAt:"2026-10-23",updatedAt:"2026-10-23",version:1,status:"Menunggu pemeriksaan",docs:{bast:{name:"BAST_Ilustrasi_Timur.pdf",ready:true},lpj:{name:"LPJ_Ilustrasi_Timur.pdf",ready:true},pptx:{name:"Dokumentasi_Ilustrasi_Timur.pptx",ready:true}}},
    {id:"SIT-VRF-023",groupId:"GRP-SL-01",group:"P3A Karya Bersama",di:"DI Karya",province:"Provinsi Ilustrasi A",regency:"Kabupaten Contoh Selatan",district:"Kecamatan Selatan",village:"Desa Karya",sentAt:"2026-10-27",updatedAt:"2026-10-29",version:1,status:"Tidak sesuai",docs:{bast:{name:"BAST_Karya.pdf",ready:true},lpj:{name:"LPJ_Karya.pdf",ready:false},pptx:{name:"Dokumentasi_Karya.pptx",ready:true}}},
    {id:"SIT-VRF-022",groupId:"GRP-BR-02",group:"Poktan Sumber Makmur",di:"DI Sumber",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Barat",district:"Kecamatan Sumber",village:"Desa Makmur",sentAt:"2026-10-26",updatedAt:"2026-10-28",version:1,status:"Sesuai",docs:{bast:{name:"BAST_Sumber_Makmur.pdf",ready:true},lpj:{name:"LPJ_Sumber_Makmur.pdf",ready:true},pptx:{name:"Dokumentasi_Sumber_Makmur.pptx",ready:true}}},
    {id:"SIT-VRF-021",groupId:"GRP-TM-02",group:"P3A Mekar Jaya",di:"DI Mekar",province:"Provinsi Ilustrasi C",regency:"Kabupaten Contoh Timur",district:"Kecamatan Mekar",village:"Desa Jaya",sentAt:"2026-10-25",updatedAt:"2026-10-27",version:2,status:"Tidak sesuai",docs:{bast:{name:"BAST_Mekar_v2.pdf",ready:true},lpj:{name:"LPJ_Mekar_v2.pdf",ready:true},pptx:{name:"Dokumentasi_Mekar_v2.pptx",ready:false}}},
    {id:"SIT-VRF-020",groupId:"GRP-SL-02",group:"P3A Tani Makmur",di:"DI Tani",province:"Provinsi Ilustrasi A",regency:"Kabupaten Contoh Selatan",district:"Kecamatan Selatan",village:"Desa Makmur",sentAt:null,updatedAt:"2026-10-30",version:0,status:"Draf",docs:{bast:{name:"BAST_Tani_Makmur.pdf",ready:true},lpj:{name:"",ready:false},pptx:{name:"",ready:false}}},
    {id:"SIT-VRF-019",groupId:"GRP-BR-03",group:"GP3A Maju Bersama",di:"DI Maju",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Barat",district:"Kecamatan Maju",village:"Desa Bersama",sentAt:null,updatedAt:"2026-10-28",version:0,status:"Draf",docs:{bast:{name:"BAST_Maju_Bersama.pdf",ready:true},lpj:{name:"LPJ_Maju_Bersama.pdf",ready:true},pptx:{name:"",ready:false}}}
  ];

  let records;
  try { const saved=JSON.parse(localStorage.getItem(STORAGE_KEY)||"null"); records=Array.isArray(saved)&&saved.length?saved:seed; }
  catch (_) { records=seed; }
  let activeView="dashboard", activeId=null, toastTimer=null, currentPage=1;
  const PAGE_SIZE=5;

  // Contoh agregat CPCL untuk pratinjau. Ganti sumber ini dengan agregasi CPCL aktual saat backend tersedia.
  const CPCL_DONUT_DATA = {
    "Nasional": {total:1840, sesuai:500, belumSesuai:340, belumMelapor:1000},
    "Kabupaten Contoh Utara": {total:480, sesuai:130, belumSesuai:80, belumMelapor:270},
    "Kabupaten Contoh Barat": {total:510, sesuai:140, belumSesuai:90, belumMelapor:280},
    "Kabupaten Contoh Timur": {total:450, sesuai:120, belumSesuai:80, belumMelapor:250},
    "Kabupaten Contoh Selatan": {total:400, sesuai:110, belumSesuai:90, belumMelapor:200}
  };
  const CPCL_DONUT_COLORS = {sesuai:"#0B3B70", belumSesuai:"#4E8CCB", belumMelapor:"#B9D2EA"};
  const formatCount = value => new Intl.NumberFormat("id-ID").format(value);

  const normalize = value => String(value||"").trim().replace(/\\s+/g," ").toLocaleLowerCase("id-ID");
  const uniqueSorted = values => [...new Set(values.filter(Boolean))].sort((a,b)=>a.localeCompare(b,"id"));
  const isReady = record => ["bast","lpj","pptx"].every(key=>record.docs[key]?.ready);
  const currentDate = () => new Date().toISOString().slice(0,10);
  const dateLabel = iso => {
    if(!iso)return "Belum dikirim";
    const d=new Date(iso+"T00:00:00");
    return d.toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"});
  };
  const elapsedDays = iso => iso ? Math.max(0,Math.floor((new Date(currentDate()+"T00:00:00")-new Date(iso+"T00:00:00"))/86400000)) : null;
  const badge = status => '<span class="status '+(status==="Sesuai"?"done":status==="Tidak sesuai"?"revision":status==="Draf"?"draft":"waiting")+'">'+esc(status)+'</span>';
  const toast = message => {
    const node=$("#toast");node.textContent=message;node.classList.add("show");
    clearTimeout(toastTimer);toastTimer=setTimeout(()=>node.classList.remove("show"),2800);
  };
  const setOptions = (id, values, placeholder, selected="") => {
    const node=$("#"+id); if(!node)return;
    const keep=values.includes(selected)?selected:"";
    node.innerHTML='<option value="">'+esc(placeholder)+'</option>'+values.map(v=>'<option value="'+esc(v)+'">'+esc(v)+'</option>').join("");
    node.value=keep;
  };
  function scopeForOptions(){
    const province=$("#filter-province").value, regency=$("#filter-regency").value, district=$("#filter-district").value, village=$("#filter-village").value;
    return records.filter(r=>(!province||r.province===province)&&(!regency||r.regency===regency)&&(!district||r.district===district)&&(!village||r.village===village));
  }
  function refreshDependentFilters(source="init"){
    const province=$("#filter-province").value, regency=$("#filter-regency").value, district=$("#filter-district").value, village=$("#filter-village").value;
    const all=records;
    if(source==="province"){
      setOptions("filter-regency",uniqueSorted(all.filter(r=>!province||r.province===province).map(r=>r.regency)),"Semua kabupaten/kota","");
      setOptions("filter-district",uniqueSorted(all.filter(r=>(!province||r.province===province)&&(!$("#filter-regency").value||r.regency===$("#filter-regency").value)).map(r=>r.district)),"Semua kecamatan","");
      setOptions("filter-village",uniqueSorted(all.filter(r=>(!province||r.province===province)&&(!$("#filter-regency").value||r.regency===$("#filter-regency").value)&&(!$("#filter-district").value||r.district===$("#filter-district").value)).map(r=>r.village)),"Semua desa/kelurahan","");
    } else if(source==="regency"){
      setOptions("filter-district",uniqueSorted(all.filter(r=>(!province||r.province===province)&&(!regency||r.regency===regency)).map(r=>r.district)),"Semua kecamatan","");
      setOptions("filter-village",uniqueSorted(all.filter(r=>(!province||r.province===province)&&(!regency||r.regency===regency)&&(!$("#filter-district").value||r.district===$("#filter-district").value)).map(r=>r.village)),"Semua desa/kelurahan","");
    } else if(source==="district"){
      setOptions("filter-village",uniqueSorted(all.filter(r=>(!province||r.province===province)&&(!regency||r.regency===regency)&&(!district||r.district===district)).map(r=>r.village)),"Semua desa/kelurahan","");
    }
    const scoped=scopeForOptions();
    setOptions("filter-group",uniqueSorted(scoped.map(r=>r.group)),"Semua kelompok tani",$("#filter-group").value);
    setOptions("filter-di",uniqueSorted(scoped.map(r=>r.di)),"Semua Nama DI",$("#filter-di").value);
  }
  function getFiltered(){
    const q=normalize($("#report-search").value), province=$("#filter-province").value, regency=$("#filter-regency").value;
    const district=$("#filter-district").value, village=$("#filter-village").value, group=$("#filter-group").value, di=$("#filter-di").value;
    const status=$("#filter-status").value, from=$("#filter-from").value, to=$("#filter-to").value, sort=$("#filter-sort").value;
    const hasDate=!!(from||to);
    let rows=records.filter(r=>{
      const dateMatch=!hasDate||(!!r.sentAt&&(!from||r.sentAt>=from)&&(!to||r.sentAt<=to));
      const textMatch=!q||[r.id,r.group,r.di].some(v=>normalize(v).includes(q));
      return textMatch&&(!province||r.province===province)&&(!regency||r.regency===regency)&&
        (!district||r.district===district)&&(!village||r.village===village)&&(!group||r.group===group)&&
        (!di||r.di===di)&&(!status||r.status===status)&&dateMatch;
    });
    rows.sort((a,b)=>{
      let d=0;
      if(sort==="kab-asc"||sort==="kab-desc"){d=a.regency.localeCompare(b.regency,"id");if(sort==="kab-desc")d=-d;}
      else if(sort==="group-asc"||sort==="group-desc"){d=a.group.localeCompare(b.group,"id");if(sort==="group-desc")d=-d;}
      else if(sort==="di-asc"||sort==="di-desc"){d=a.di.localeCompare(b.di,"id");if(sort==="di-desc")d=-d;}
      else {d=a.updatedAt.localeCompare(b.updatedAt);if(sort!=="updated-asc")d=-d;}
      return d||a.id.localeCompare(b.id,"id");
    });
    return rows;
  }
  function filterSummary(){
    const labels=[];
    const pairs=[["filter-province","Provinsi"],["filter-regency","Kabupaten"],["filter-district","Kecamatan"],["filter-village","Desa"],["filter-group","Kelompok"],["filter-di","DI"],["filter-status","Status"]];
    pairs.forEach(([id,label])=>{const value=$("#"+id).value;if(value)labels.push(label+": "+value);});
    if($("#filter-from").value||$("#filter-to").value)labels.push("Tanggal kirim: "+($("#filter-from").value||"awal")+" – "+($("#filter-to").value||"terakhir"));
    if($("#report-search").value.trim())labels.push("Pencarian: "+$("#report-search").value.trim());
    return labels.length?labels.join(" · "):"Semua wilayah · Semua status";
  }
  function updateFeedback(rows){
    $("#filter-summary").textContent=filterSummary();
    $("#result-count").textContent=rows.length+" paket";
  }
  function renderMetrics(rows){
    const count=status=>rows.filter(r=>r.status===status).length;
    const waiting=rows.filter(r=>r.status==="Menunggu pemeriksaan").sort((a,b)=>a.sentAt.localeCompare(b.sentAt));
    const longest=waiting[0]?elapsedDays(waiting[0].sentAt):null;
    $("#leader-metrics").innerHTML=[
      metric("Total paket",rows.length,"ID paket unik pada filter aktif","▤",""),
      metric("Draf",count("Draf"),"Belum dikirim · bukan antrean","✎",""),
      metric("Menunggu pemeriksaan",count("Menunggu pemeriksaan"),"Menunggu hasil Verifikator","◷","amber"),
      metric("Tidak sesuai",count("Tidak sesuai"),"Memerlukan tindak lanjut Pelapor","!","red"),
      metric("Sesuai",count("Sesuai"),"Pemeriksaan telah selesai","✓","green")
    ].join("");
  }
  function metric(label,value,meta,icon,cls){
    return '<article class="metric '+cls+'"><div class="metric-top"><span>'+label+'</span><span class="metric-icon">'+icon+'</span></div><div class="metric-value">'+value+'</div><div class="metric-meta">'+meta+'</div></article>';
  }
  function renderStatusChart(){
    const scope=$("#cpcl-chart-regency").value||"Nasional";
    const data=CPCL_DONUT_DATA[scope]||CPCL_DONUT_DATA.Nasional;
    const segments=[
      {key:"sesuai",label:"BAST sudah sesuai",count:data.sesuai,color:CPCL_DONUT_COLORS.sesuai},
      {key:"belumSesuai",label:"BAST belum sesuai",count:data.belumSesuai,color:CPCL_DONUT_COLORS.belumSesuai},
      {key:"belumMelapor",label:"Poktan belum melapor",count:data.belumMelapor,color:CPCL_DONUT_COLORS.belumMelapor}
    ];
    const circumference=2*Math.PI*78;
    let offset=0;
    const arcs=segments.map(item=>{
      const length=data.total?circumference*(item.count/data.total):0;
      const visible=Math.max(0,length-1.4);
      const circle='<circle cx="110" cy="110" r="78" fill="none" stroke="'+item.color+'" stroke-width="28" stroke-dasharray="'+visible+' '+(circumference-visible)+'" stroke-dashoffset="'+(-offset)+'"></circle>';
      offset+=length;
      return circle;
    }).join("");
    $("#cpcl-donut-svg").innerHTML='<circle cx="110" cy="110" r="78" fill="none" stroke="#edf3f8" stroke-width="28"></circle><g transform="rotate(-90 110 110)">'+arcs+'</g>';
    $("#cpcl-total").textContent=formatCount(data.total);
    $("#cpcl-scope-label").textContent=scope==="Nasional"?"Seluruh Indonesia":scope;
    $("#cpcl-donut-legend").innerHTML=segments.map(item=>'<div class="cpcl-donut-item"><span class="cpcl-donut-dot" style="background:'+item.color+'"></span><span class="cpcl-donut-name">'+item.label+'</span><strong class="cpcl-donut-value">'+formatCount(item.count)+'</strong></div>').join("");
    $(".cpcl-donut").setAttribute("aria-label","Diagram donat status CPCL untuk "+scope+". Total "+formatCount(data.total)+" CPCL.");
  }
  function renderCompleteness(rows){
    const categories=[["BAST","bast"],["LPJ","lpj"],["Dokumentasi PPTX","pptx"]];
    $("#completeness-list").innerHTML=categories.map(([label,key])=>{
      const count=rows.filter(r=>r.docs[key]?.ready).length, percent=rows.length?Math.round(count/rows.length*100):0;
      return '<div class="complete-row"><div class="complete-top"><span>'+label+'</span><strong>'+count+' / '+rows.length+' paket</strong></div><div class="complete-track"><span style="width:'+percent+'%"></span></div></div>';
    }).join("")+'<div class="completeness-summary">Paket dengan ketiga bagian tersedia: '+rows.filter(isReady).length+' / '+rows.length+'. Ketersediaan dokumen bukan hasil pemeriksaan.</div>';
  }
  function compactTable(rows,kind){
    if(!rows.length)return '<div class="empty-state">'+(kind==="waiting"?"Tidak ada antrean pemeriksaan pada filter ini.":"Tidak ada paket Tidak sesuai pada filter ini.")+'</div>';
    const isWaiting=kind==="waiting";
    const headers=isWaiting?["ID laporan","Kelompok tani","Kabupaten/Kota","Tanggal kirim","Lama menunggu"]:["ID laporan","Kelompok tani","Kabupaten/Kota","Pembaruan terakhir","Status"];
    const body=rows.map(r=>isWaiting
      ? '<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.group)+'</span></td><td>'+esc(r.regency)+'</td><td>'+dateLabel(r.sentAt)+'</td><td><span class="wait-age">'+elapsedDays(r.sentAt)+' hari</span></td></tr>'
      : '<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.group)+'</span></td><td>'+esc(r.regency)+'</td><td>'+dateLabel(r.updatedAt)+'</td><td>'+badge(r.status)+'</td></tr>'
    ).join("");
    return '<div class="table-wrap compact-table"><table class="data-table"><thead><tr>'+headers.map(h=>'<th>'+h+'</th>').join("")+'</tr></thead><tbody>'+body+'</tbody></table></div><div class="table-bottom">Menampilkan '+rows.length+' paket · data simulasi</div>';
  }
  function renderDashboard(rows){
    renderMetrics(rows);renderStatusChart();renderCompleteness(rows);
    const waiting=rows.filter(r=>r.status==="Menunggu pemeriksaan").sort((a,b)=>a.sentAt.localeCompare(b.sentAt)||a.id.localeCompare(b.id)).slice(0,5);
    const followup=rows.filter(r=>r.status==="Tidak sesuai").sort((a,b)=>b.updatedAt.localeCompare(a.updatedAt));
    $("#oldest-queue").innerHTML=compactTable(waiting,"waiting");
    $("#followup-list").innerHTML=compactTable(followup,"followup");
  }
  function reportTable(rows){
    if(!rows.length)return '<div class="empty-state">Tidak ada paket yang cocok dengan filter. Coba ubah filter atau gunakan Reset filter.</div>';
    const pageCount=Math.max(1,Math.ceil(rows.length/PAGE_SIZE));
    currentPage=Math.min(currentPage,pageCount);
    const start=(currentPage-1)*PAGE_SIZE;
    const visibleRows=rows.slice(start,start+PAGE_SIZE);
    const body=visibleRows.map(r=>{
      const docs=["bast","lpj","pptx"].filter(key=>r.docs[key]?.ready).length;
      return '<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.group)+'</span><span class="cell-sub">'+esc(r.groupId)+'</span></td><td>'+esc(r.di)+'</td><td>'+esc(r.province)+'</td><td>'+esc(r.regency)+'</td><td>'+dateLabel(r.sentAt)+'</td><td>'+badge(r.status)+'</td><td><span class="doc-completeness '+(docs===3?"complete":"")+'">'+docs+'/3</span></td><td><button class="action-link" data-action="open-detail" data-id="'+esc(r.id)+'">Detail →</button></td></tr>';
    }).join("");
    const pageButtons=Array.from({length:pageCount},(_,i)=>'<button class="page-button '+(currentPage===i+1?"active":"")+'" data-action="go-page" data-page="'+(i+1)+'" aria-label="Halaman '+(i+1)+'">'+(i+1)+'</button>').join("");
    return '<div class="table-wrap"><table class="data-table"><thead><tr><th>ID laporan</th><th>Kelompok tani</th><th>Nama DI</th><th>Provinsi</th><th>Kabupaten/Kota</th><th>Tanggal kirim</th><th>Status terkini</th><th>Dokumen</th><th>Aksi</th></tr></thead><tbody>'+body+'</tbody></table></div><div class="table-bottom"><span>Menampilkan '+(start+1)+'–'+Math.min(start+PAGE_SIZE,rows.length)+' dari '+rows.length+' paket · Draf tanpa tanggal kirim dikecualikan dari filter rentang tanggal.</span><span class="pagination"><button class="page-button" data-action="go-page" data-page="'+Math.max(1,currentPage-1)+'" '+(currentPage===1?"disabled":"")+' aria-label="Halaman sebelumnya">‹</button>'+pageButtons+'<button class="page-button" data-action="go-page" data-page="'+Math.min(pageCount,currentPage+1)+'" '+(currentPage===pageCount?"disabled":"")+' aria-label="Halaman berikutnya">›</button></span></div>';
  }
  function renderReports(rows){$("#reports-table").innerHTML=reportTable(rows);}
  function renderDetail(id){
    const r=records.find(x=>x.id===id);if(!r)return;
    activeId=id;
    $("#detail-id").textContent=r.id;
    $("#detail-subtitle").textContent=r.group+" · "+r.di+" · "+r.regency;
    $("#detail-status").innerHTML=badge(r.status);
    $("#detail-version").textContent="Versi "+(r.version||1);
    const fields=[["Kelompok tani",r.group],["Nama DI",r.di],["Provinsi",r.province],["Kabupaten/Kota",r.regency],["Kecamatan",r.district],["Desa/Kelurahan",r.village],["Tanggal kirim",dateLabel(r.sentAt)],["Pembaruan terakhir",dateLabel(r.updatedAt)],["Status terkini",r.status]];
    $("#detail-fields").innerHTML=fields.map(([label,value])=>'<div class="detail-field"><small>'+esc(label)+'</small><strong>'+esc(value||"—")+'</strong></div>').join("");
    const docs=[["BAST","PDF","bast"],["LPJ","PDF","lpj"],["Dokumentasi","PPTX","pptx"]];
    $("#detail-documents").innerHTML=docs.map(([label,type,key])=>{
      const doc=r.docs[key],ready=!!doc?.ready;
      return '<div class="review-document"><span class="review-document-mark '+(type==="PPTX"?"pptx":"")+'">'+type+'</span><span class="review-document-copy"><strong>'+esc(ready?doc.name:"Belum tersedia")+'</strong><small>'+label+' · '+type+'</small></span><span class="review-document-meta">'+(ready?'<span class="doc-completeness complete">Tersedia</span>':'<span class="doc-completeness">Belum ada</span>')+'</span></div>';
    }).join("");
    setView("detail");
  }
  function updateAll(resetPage=false){
    if(resetPage)currentPage=1;
    const rows=getFiltered();updateFeedback(rows);
    if(activeView==="dashboard")renderDashboard(rows);
    if(activeView==="reports")renderReports(rows);
  }
  function setView(view){
    activeView=view;
    ["dashboard","reports","detail"].forEach(name=>$("#view-"+name).hidden=name!==view);
    document.querySelectorAll(".nav-item").forEach(node=>node.classList.toggle("active",node.dataset.view===view));
    const labels={dashboard:["Ringkasan","Ringkasan rekapitulasi","Pantau status terkini paket laporan dari seluruh wilayah sesuai izin akun."],reports:["Rekap Laporan","Rekap paket laporan","Telusuri laporan lintas wilayah berdasarkan filter aktif."]};
    if(labels[view]){$("#breadcrumb-current").textContent=labels[view][0];$("#page-title").textContent=labels[view][1];$("#page-description").textContent=labels[view][2];}
    if(view==="detail"){$("#breadcrumb-current").textContent="Detail laporan";$("#page-title").textContent="Detail paket laporan";$("#page-description").textContent="Ringkasan identitas paket dan ketersediaan dokumen.";}
    updateAll();window.scrollTo({top:0,behavior:"smooth"});
  }
  function resetFilters(){
    ["report-search","filter-province","filter-regency","filter-district","filter-village","filter-group","filter-di","filter-status","filter-from","filter-to"].forEach(id=>$("#"+id).value="");
    $("#filter-sort").value="updated-desc";refreshDependentFilters("province");updateAll(true);
  }
  document.addEventListener("click",event=>{
    const nav=event.target.closest("[data-view]");if(nav){setView(nav.dataset.view);return;}
    const action=event.target.closest("[data-action]");if(!action)return;
    switch(action.dataset.action){
      case "reset-filters":resetFilters();break;
      case "go-reports":setView("reports");break;
      case "back-reports":setView("reports");break;
      case "open-detail":renderDetail(action.dataset.id);break;
      case "go-page":currentPage=Number(action.dataset.page)||1;updateAll(false);break;
    }
  });
  $("#filter-province").addEventListener("change",()=>{refreshDependentFilters("province");updateAll(true);});
  $("#filter-regency").addEventListener("change",()=>{refreshDependentFilters("regency");updateAll(true);});
  $("#filter-district").addEventListener("change",()=>{refreshDependentFilters("district");updateAll(true);});
  $("#filter-village").addEventListener("change",()=>{refreshDependentFilters("village");updateAll(true);});
  ["report-search","filter-group","filter-di","filter-status","filter-from","filter-to","filter-sort"].forEach(id=>{
    $("#"+id).addEventListener("input",()=>updateAll(true));$("#"+id).addEventListener("change",()=>updateAll(true));
  });
  $("#cpcl-chart-regency").addEventListener("change",renderStatusChart);
  $("#notification-button").addEventListener("click",()=>toast("Notifikasi belum termasuk baseline inti SRS v0.12."));
  function init(){
    setOptions("filter-province",uniqueSorted(records.map(r=>r.province)),"Semua provinsi");
    refreshDependentFilters("province");
    updateAll();
  }
  init();
})();