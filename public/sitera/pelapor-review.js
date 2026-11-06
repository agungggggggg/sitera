(() => {
  "use strict";
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const esc = value => String(value ?? "").replace(/[&<>"']/g, ch => ({
    "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"
  }[ch]));
  const formatSize = bytes => bytes < 1024 * 1024
    ? (bytes / 1024).toFixed(0) + " KB"
    : (bytes / (1024 * 1024)).toFixed(1) + " MB";

  const cpcl = [
    {id:"CP-DEMO-001",no:"1",di:"DI Contoh Barat",kewenangan:"Data simulasi",keterangan:"Data simulasi",provinsi:"Provinsi Ilustrasi",kab:"Kabupaten Demo",kecamatan:"Kecamatan Utara",desa:"Desa Contoh",kelompok:"P3A Tirta Contoh",ketua:"Data ilustrasi",realisasi:"Belum dikonfirmasi",luasHa:"Belum dikonfirmasi",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
    {id:"CP-DEMO-002",no:"2",di:"DI Sumber Demo",kewenangan:"Data simulasi",keterangan:"Data simulasi",provinsi:"Provinsi Ilustrasi",kab:"Kabupaten Demo",kecamatan:"Kecamatan Timur",desa:"Desa Sumber",kelompok:"Poktan Sumber Demo",ketua:"Data ilustrasi",realisasi:"Belum dikonfirmasi",luasHa:"Belum dikonfirmasi",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
    {id:"CP-DEMO-003",no:"3",di:"DI Karya Ilustrasi",kewenangan:"Data simulasi",keterangan:"Data simulasi",provinsi:"Provinsi Ilustrasi",kab:"Kabupaten Demo",kecamatan:"Kecamatan Selatan",desa:"Desa Karya",kelompok:"P3A Karya Ilustrasi",ketua:"Data ilustrasi",realisasi:"Belum dikonfirmasi",luasHa:"Belum dikonfirmasi",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
    {id:"CP-DEMO-004",no:"4",di:"DI Makmur Demo",kewenangan:"Data simulasi",keterangan:"Data simulasi",provinsi:"Provinsi Ilustrasi",kab:"Kabupaten Demo",kecamatan:"Kecamatan Utara",desa:"Desa Makmur",kelompok:"Poktan Tani Makmur",ketua:"Data ilustrasi",realisasi:"Belum dikonfirmasi",luasHa:"Belum dikonfirmasi",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
    {id:"CP-DEMO-005",no:"5",di:"DI Harapan Contoh",kewenangan:"Data simulasi",keterangan:"Data simulasi",provinsi:"Provinsi Ilustrasi",kab:"Kabupaten Demo",kecamatan:"Kecamatan Timur",desa:"Desa Harapan",kelompok:"GP3A Harapan Tirta",ketua:"Data ilustrasi",realisasi:"Belum dikonfirmasi",luasHa:"Belum dikonfirmasi",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}
  ];
  let reports = [
    {id:"SIT-DEM-001",cpclId:"CP-DEMO-001",kelompok:"P3A Tirta Contoh",di:"DI Contoh Barat",kecamatan:"Kecamatan Utara",desa:"Desa Contoh",date:"29 Okt 2026",updatedISO:"2026-10-29",sentAt:"2026-10-29",period:"2026",status:"Menunggu pemeriksaan",version:1,files:{bast:"BAST-tirta-contoh.pdf",lpj:"LPJ-tirta-contoh.pdf",doc:"Dokumentasi-tirta-contoh.pptx"},feedback:"",history:[{title:"Laporan dikirim",detail:"Laporan diterima sistem dan masuk antrean pemeriksaan.",date:"29 Okt 2026"}]},
    {id:"SIT-DEM-002",cpclId:"CP-DEMO-002",kelompok:"Poktan Sumber Demo",di:"DI Sumber Demo",kecamatan:"Kecamatan Timur",desa:"Desa Sumber",date:"27 Okt 2026",updatedISO:"2026-10-27",sentAt:"2026-10-25",period:"2026",status:"Tidak sesuai",version:1,files:{bast:"BAST-sumber-demo.pdf",lpj:"LPJ-sumber-demo.pdf",doc:"Dokumentasi-sumber-demo.pptx"},feedback:"Mohon cocokkan identitas kelompok pada BAST dengan data CPCL dan unggah kembali dokumen yang telah diperbaiki.",history:[{title:"Laporan dikirim",detail:"Laporan diterima sistem.",date:"25 Okt 2026"},{title:"Tidak sesuai",detail:"Verifikator memberikan catatan perbaikan.",date:"27 Okt 2026"}]},
    {id:"SIT-DEM-003",cpclId:"CP-DEMO-003",kelompok:"P3A Karya Ilustrasi",di:"DI Karya Ilustrasi",kecamatan:"Kecamatan Selatan",desa:"Desa Karya",date:"25 Okt 2026",updatedISO:"2026-10-25",sentAt:"2026-10-22",period:"2026",status:"Sesuai",version:1,files:{bast:"BAST-karya-ilustrasi.pdf",lpj:"LPJ-karya-ilustrasi.pdf",doc:"Dokumentasi-karya-ilustrasi.pptx"},feedback:"",history:[{title:"Laporan dikirim",detail:"Laporan diterima sistem.",date:"22 Okt 2026"},{title:"Sesuai",detail:"Pemeriksaan dokumen selesai.",date:"25 Okt 2026"}]},
    {id:"SIT-DEM-004",cpclId:"CP-DEMO-004",kelompok:"Poktan Tani Makmur",di:"DI Makmur Demo",kecamatan:"Kecamatan Utara",desa:"Desa Makmur",date:"22 Okt 2026",updatedISO:"2026-10-22",sentAt:null,period:"2026",status:"Draf",version:0,files:{bast:"BAST-draf.pdf",lpj:"",doc:""},feedback:"",history:[{title:"Draf dibuat",detail:"Pelapor mulai menyusun paket laporan.",date:"22 Okt 2026"}]}
  ];
  const allReports = [
    {id:"SIT-DEM-101",prov:"Provinsi Ilustrasi A",kab:"Kabupaten Contoh Utara",kecamatan:"Kecamatan Demo Utara",desa:"Desa Ilustrasi",kelompok:"P3A Demo Utara",di:"DI Contoh Utara",status:"Sesuai",sentAt:"2026-10-26",updatedISO:"2026-10-26",date:"26 Okt 2026"},
    {id:"SIT-DEM-102",prov:"Provinsi Ilustrasi B",kab:"Kabupaten Contoh Barat",kecamatan:"Kecamatan Demo Barat",desa:"Desa Simulasi",kelompok:"Poktan Contoh Barat",di:"DI Contoh Barat",status:"Tidak sesuai",sentAt:"2026-10-27",updatedISO:"2026-10-27",date:"27 Okt 2026"},
    {id:"SIT-DEM-103",prov:"Provinsi Ilustrasi C",kab:"Kabupaten Contoh Timur",kecamatan:"Kecamatan Demo Timur",desa:"Desa Rekaan",kelompok:"GP3A Ilustrasi Timur",di:"DI Contoh Timur",status:"Menunggu pemeriksaan",sentAt:"2026-10-28",updatedISO:"2026-10-28",date:"28 Okt 2026"}
  ];
  reports.forEach(report => {
    if (!Array.isArray(report.versions)) {
      report.versions = report.version > 0 ? [{
        number: report.version, files: {...report.files}, status: report.status,
        date: report.date, feedback: report.feedback || ""
      }] : [];
    }
  });
  let currentCpclId = null;
  let currentView = "dashboard";
  let currentReportId = null;
  let formState = {cpclId:null, reportId:null, revision:false, files:{}};
  let toastTimer = null;

  const statusClass = status => ({
    "Draf":"draft",
    "Menunggu pemeriksaan":"waiting",
    "Tidak sesuai":"revision",
    "Sesuai":"done"
  }[status] || "draft");
  const statusBadge = status => '<span class="status '+statusClass(status)+'">'+esc(status)+'</span>';

  function showToast(message) {
    const node = $("#toast");
    node.textContent = message;
    node.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => node.classList.remove("show"), 2800);
  }
  function navigate(view) {
    currentView = view;
    const meta = {
      dashboard:["Ringkasan","Ringkasan laporan","Pantau status laporan kegiatan irigasi kabupaten Anda."],
      reports:["Laporan Saya","Laporan Saya","Kelola dan telusuri paket laporan milik kabupaten Anda."],
      cpcl:["Pilih CPCL","Pilih Data CPCL","Pilih kelompok tani yang menjadi dasar paket laporan."],
      all:["Semua Laporan","Ringkasan lintas kabupaten","Lihat ringkasan agregat tanpa membuka dokumen atau catatan internal daerah lain."],
      form:["Formulir Laporan","Buat Laporan Kegiatan","Lengkapi tiga bagian dokumen untuk mengirim laporan."],
      detail:["Detail Laporan","Detail laporan","Lihat identitas, dokumen, status dan histori paket laporan."]
    }[view] || ["Ringkasan","Ringkasan laporan","Pantau laporan Anda."];
    $("#breadcrumb-current").textContent = meta[0];
    $("#page-title").textContent = meta[1];
    $("#page-description").textContent = meta[2];
    $$(".nav-item").forEach(btn => btn.classList.toggle("active",btn.dataset.view === view || (view === "detail" && btn.dataset.view === "reports") || (view === "form" && btn.dataset.view === "cpcl")));
    $$(".view").forEach(section => {
      const on = section.id === "view-" + view;
      section.hidden = !on;
      section.classList.toggle("active",on);
    });
    $("#page-title").scrollIntoView({block:"nearest"});
    if (view === "dashboard") renderDashboard();
    if (view === "reports") renderReports();
    if (view === "cpcl") renderCpcl();
    if (view === "all") renderAllReports();
  }

  function metric(label,value,sub,icon,extra="") {
    return '<article class="metric '+extra+'"><div class="metric-top"><span>'+label+'</span><span class="metric-icon">'+icon+'</span></div><div class="metric-value">'+value+'</div><div class="metric-meta">'+sub+'</div></article>';
  }
  function renderDashboard() {
    const total = reports.length;
    const count = status => reports.filter(r=>r.status===status).length;
    $("#dashboard-metrics").innerHTML = [
      metric("Total laporan",total,"Paket laporan pada akun ini","▤"),
      metric("Draf",count("Draf"),"Belum dikirim","✎"),
      metric("Menunggu pemeriksaan",count("Menunggu pemeriksaan"),"Sedang dalam antrean","◷"),
      metric("Tidak sesuai",count("Tidak sesuai"),"Memerlukan tindak lanjut","!", "red"),
      metric("Sesuai",count("Sesuai"),"Pemeriksaan selesai","✓", "green")
    ].join("");
    const latest = [...reports].sort((a,b)=>b.date.localeCompare(a.date)).slice(0,4);
    $("#activity-list").innerHTML = latest.map(r=>{
      const cls = r.status==="Sesuai"?"green":r.status==="Tidak sesuai"?"amber":"";
      const icon = r.status==="Sesuai"?"✓":r.status==="Tidak sesuai"?"!":r.status==="Draf"?"✎":"◷";
      return '<div class="activity-item"><span class="activity-mark '+cls+'">'+icon+'</span><div class="activity-copy"><strong>'+esc(r.id)+' · '+esc(r.kelompok)+'</strong><small>'+esc(r.status)+' · '+esc(r.di)+'</small></div><span class="activity-date">'+esc(r.date)+'</span></div>';
    }).join("");
    const complete = key => reports.filter(r=>!!r.files[key]).length;
    [["bast","complete-bast","bar-bast"],["lpj","complete-lpj","bar-lpj"],["doc","complete-doc","bar-doc"]].forEach(([key,label,bar])=>{
      const value=complete(key),pct=total?Math.round(value/total*100):0;
      $("#"+label).textContent=value+" dari "+total;
      $("#"+bar).style.width=pct+"%";
    });
    $("#dashboard-table").innerHTML = tableMarkup(latest,false);
  }
  function tableMarkup(rows,showPeriod=true) {
    if (!rows.length) return '<div class="empty-state">Tidak ada laporan yang cocok dengan pencarian.</div>';
    return '<div class="table-wrap"><table class="data-table"><thead><tr><th>ID Laporan</th><th>Kelompok Tani</th><th>Daerah Irigasi</th><th>Terakhir Diperbarui</th><th>Status</th><th>Aksi</th></tr></thead><tbody>'+
      rows.map(r=>'<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.kelompok)+'</span><span class="cell-sub">'+esc(r.kecamatan||"")+'</span></td><td>'+esc(r.di)+'</td><td>'+esc(r.date)+'</td><td>'+statusBadge(r.status)+'</td><td><button class="action-link" data-action="detail" data-id="'+esc(r.id)+'">Detail →</button></td></tr>').join("")+
      '</tbody></table></div><div class="table-bottom"><span>Menampilkan '+rows.length+' paket pada data simulasi</span><span class="pagination"><button class="page-button active">1</button><button class="page-button" disabled>2</button><button class="page-button" disabled>›</button></span></div>';
  }
  function renderReports() {
    const q = ($("#report-search")?.value||"").toLowerCase().trim();
    const status = $("#status-filter")?.value||"";
    const province = $("#report-province")?.value||"";
    const group = $("#report-group")?.value||"";
    const di = $("#report-di")?.value||"";
    const district = $("#report-district")?.value||"";
    const village = $("#report-village")?.value||"";
    const from = $("#report-date-from")?.value||"";
    const to = $("#report-date-to")?.value||"";
    const sort = $("#report-sort")?.value||"date-desc";
    let rows=reports.filter(r=>{
      const inRange=(!from&&!to)||(!!r.sentAt&&(!from||r.sentAt>=from)&&(!to||r.sentAt<=to));
      const c=cpcl.find(item=>item.id===r.cpclId)||{};
      return (!q||[r.id,r.kelompok,r.di,r.kecamatan,r.desa].some(v=>String(v||"").toLowerCase().includes(q))) &&
        (!status||r.status===status) && (!province||c.provinsi===province) &&
        (!group||r.kelompok===group) && (!di||r.di===di) &&
        (!district||r.kecamatan===district) && (!village||r.desa===village) && inRange;
    });
    rows.sort((a,b)=>{
      if(sort==="group-asc"||sort==="group-desc"){const d=a.kelompok.localeCompare(b.kelompok,"id");return sort==="group-asc"?d:-d;}
      if(sort==="di-asc"||sort==="di-desc"){const d=a.di.localeCompare(b.di,"id");return sort==="di-asc"?d:-d;}
      const diff=String(a.updatedISO||"").localeCompare(String(b.updatedISO||""));
      return sort==="date-asc"?diff:-diff;
    });
    $("#reports-table").innerHTML=tableMarkup(rows);
  }
  function renderCpcl() {
    const q=($("#cpcl-search")?.value||"").toLowerCase().trim();
    const prov=($("#cpcl-province")?.value||"");
    const kab=($("#cpcl-regency")?.value||"");
    const kec=($("#cpcl-district")?.value||"");
    const rows=cpcl.filter(c=>(!q||[c.di,c.kelompok,c.id,c.desa,c.keterangan,c.kewenangan].some(v=>String(v||"").toLowerCase().includes(q)))&&(!prov||c.provinsi===prov)&&(!kab||c.kab===kab)&&(!kec||c.kecamatan===kec));
    $("#cpcl-table").innerHTML = rows.length ? '<div class="table-wrap"><table class="data-table"><thead><tr><th>No.</th><th>Nama DI</th><th>Kewenangan</th><th>Keterangan</th><th>Provinsi</th><th>Kab/Kota</th><th>Kecamatan</th><th>Desa/Kelurahan</th><th>Kel_Tani</th><th>Status laporan</th><th>Aksi</th></tr></thead><tbody>'+
      rows.map(c=>{
        const exists=reports.find(r=>r.cpclId===c.id);
        return '<tr><td>'+esc(c.no)+'</td><td><span class="cell-main">'+esc(c.di)+'</span><span class="cell-sub">'+esc(c.id)+'</span></td><td>'+esc(c.kewenangan)+'</td><td>'+esc(c.keterangan)+'</td><td>'+esc(c.provinsi)+'</td><td>'+esc(c.kab)+'</td><td>'+esc(c.kecamatan)+'</td><td>'+esc(c.desa)+'</td><td><span class="cell-main">'+esc(c.kelompok)+'</span></td><td>'+(exists?statusBadge(exists.status):'<span class="status draft">Belum ada laporan</span>')+'</td><td><span class="cpcl-actions"><button class="action-link" data-action="detail-cpcl" data-id="'+esc(c.id)+'">Detail</button><button class="action-link" data-action="select-cpcl" data-id="'+esc(c.id)+'">'+(exists?"Buka":"Pilih")+' →</button></span></td></tr>';
      }).join("")+'</tbody></table></div><div class="table-bottom"><span>'+rows.length+' data CPCL simulasi</span><span class="pagination"><button class="page-button active">1</button></span></div>' : '<div class="empty-state">Data CPCL tidak ditemukan.</div>';
  }
  function openCpclDetail(id) {
    const c=cpcl.find(x=>x.id===id);
    if(!c)return;
    currentCpclId=id;
    const fields=[
      ["No.",c.no],["Nama DI",c.di],["Kewenangan",c.kewenangan],["Keterangan",c.keterangan],
      ["Provinsi",c.provinsi],["Kab_Kota",c.kab],["Kecamatan",c.kecamatan],
      ["Desa/Kelurahan",c.desa],["Kel_Tani",c.kelompok],["Nama Ketua",c.ketua],
      ["Realisasi",c.realisasi],["Luas_ha",c.luasHa],["X",c.x],["Y",c.y]
    ];
    $("#cpcl-detail-title").textContent="Detail CPCL · "+c.id;
    $("#cpcl-detail-fields").innerHTML=fields.map(([label,value])=>'<div class="detail-field"><small>'+esc(label)+'</small><strong>'+esc(value||"Belum tersedia")+'</strong></div>').join("");
    $("#cpcl-detail-choose").dataset.id=id;
    $("#cpcl-detail-panel").hidden=false;
    $("#cpcl-detail-panel").scrollIntoView({block:"nearest",behavior:"smooth"});
  }

  function renderAllReports() {
    const q=($("#all-search")?.value||"").toLowerCase().trim();
    const province=$("#all-province")?.value||"";
    const regency=$("#all-regency")?.value||"";
    const status=$("#all-status")?.value||"";
    const from=$("#all-date-from")?.value||"";
    const to=$("#all-date-to")?.value||"";
    const sort=$("#all-sort")?.value||"date-desc";
    let rows=allReports.filter(r=>
      (!q||[r.id,r.prov,r.kab,r.kelompok,r.di].some(v=>String(v||"").toLowerCase().includes(q))) &&
      (!province||r.prov===province) && (!regency||r.kab===regency) &&
      (!status||r.status===status) && (!from||r.sentAt>=from) && (!to||r.sentAt<=to)
    );
    rows.sort((a,b)=>{
      if(sort==="kab-asc"||sort==="kab-desc"){const d=a.kab.localeCompare(b.kab,"id");return sort==="kab-asc"?d:-d;}
      if(sort==="group-asc"||sort==="group-desc"){const d=a.kelompok.localeCompare(b.kelompok,"id");return sort==="group-asc"?d:-d;}
      if(sort==="di-asc"||sort==="di-desc"){const d=a.di.localeCompare(b.di,"id");return sort==="di-asc"?d:-d;}
      const aDate=a.updatedISO||a.sentAt,bDate=b.updatedISO||b.sentAt;
      return sort==="date-asc"?aDate.localeCompare(bDate):bDate.localeCompare(aDate);
    });
    $("#all-table").innerHTML=rows.length?'<div class="table-wrap"><table class="data-table"><thead><tr><th>ID Laporan</th><th>Kelompok Tani</th><th>Nama DI</th><th>Wilayah</th><th>Tanggal kirim</th><th>Status</th></tr></thead><tbody>'+
      rows.map(r=>'<tr><td><span class="ticket">'+esc(r.id)+'</span></td><td><span class="cell-main">'+esc(r.kelompok)+'</span></td><td>'+esc(r.di)+'</td><td><span class="cell-main">'+esc(r.kab)+'</span><span class="cell-sub">'+esc(r.prov)+' · '+esc(r.kecamatan)+' · '+esc(r.desa)+'</span></td><td>'+esc(r.date)+'</td><td>'+statusBadge(r.status)+'</td></tr>').join("")+'</tbody></table></div><div class="table-bottom"><span>Menampilkan '+rows.length+' paket ringkasan berizin. Dokumen dan catatan internal tidak ditampilkan.</span><span class="pagination"><button class="page-button active">1</button></span></div>':'<div class="empty-state">Tidak ada ringkasan yang cocok dengan filter.</div>';
  }

  function startForm(cpclId,reportId=null,revision=false) {
    const c=cpcl.find(x=>x.id===cpclId);
    if(!c){showToast("Data CPCL tidak ditemukan pada demo.");return;}
    formState={cpclId,reportId,revision,files:{}};
    ["bast","lpj","doc"].forEach(key=>{
      const input=$("#file-"+key);input.value="";
      $("#name-"+key).textContent = "Pilih "+({bast:"dokumen BAST",lpj:"dokumen LPJ",doc:"dokumentasi kegiatan"}[key]);
      $("#hint-"+key).textContent = key==="doc"?"Format PPTX · Maksimal 25.000.000 byte per berkas":"Format PDF · Batas ukuran/jumlah mengikuti keputusan final klien";
    });
    const report=reportId?reports.find(r=>r.id===reportId):null;
    $("#form-heading").textContent=revision?"Perbaiki dokumen laporan":"Dokumen pelaporan";
    $("#form-state").textContent=revision?"Revisi · Versi "+((report?.version||1)+1):report?"Lanjutkan draf":"Draf baru";
    $("#selected-cpcl").innerHTML=[
      ["Nama DI",c.di],["Kelompok Tani",c.kelompok],["Wilayah",c.kecamatan+" · "+c.desa]
    ].map(([label,val])=>'<div><small>'+label+'</small><strong>'+esc(val)+'</strong></div>').join("");
    if(revision&&report){
      ["bast","lpj","doc"].forEach(key=>{
        const f=report.files[key];
        if(f){$("#name-"+key).textContent="Berkas saat ini: "+f;$("#hint-"+key).textContent="Pilih berkas baru hanya pada bagian yang ingin diperbaiki.";}
      });
    } else if(report){
      ["bast","lpj","doc"].forEach(key=>{if(report.files[key])$("#name-"+key).textContent="Tersimpan: "+report.files[key];});
    }
    updateChecklist();
    $("#form-error").hidden=true;
    navigate("form");
  }
  function fileInputChanged(key) {
    const input=$("#file-"+key),file=input.files&&input.files[0];
    if(!file){delete formState.files[key];updateChecklist();return;}
    const ext=file.name.split(".").pop().toLowerCase();
    if(key==="bast"||key==="lpj"){
      if(ext!=="pdf"){input.value="";delete formState.files[key];showFormError("Dokumen "+key.toUpperCase()+" harus berformat PDF.");updateChecklist();return;}
    }else{
      if(ext!=="pptx"){input.value="";delete formState.files[key];showFormError("Dokumentasi harus berformat PPTX.");updateChecklist();return;}
      if(file.size>25000000){input.value="";delete formState.files[key];showFormError("Ukuran dokumentasi melebihi 25.000.000 byte.");updateChecklist();return;}
    }
    formState.files[key]=file;
    $("#name-"+key).textContent=file.name;
    $("#hint-"+key).textContent=formatSize(file.size)+" · Berkas dipilih di browser (belum dikirim ke server)";
    $("#form-error").hidden=true;
    updateChecklist();
  }
  function updateChecklist() {
    const report=formState.reportId?reports.find(r=>r.id===formState.reportId):null;
    ["bast","lpj","doc"].forEach(key=>{
      const selected=!!formState.files[key]||!!(report&&report.files[key]);
      $("#check-"+key).classList.toggle("complete",selected);
      $("#check-"+key).querySelector("span").textContent=selected?"✓":"○";
    });
  }
  function showFormError(message){const box=$("#form-error");box.textContent=message;box.hidden=false;box.scrollIntoView({block:"nearest",behavior:"smooth"});}
  function saveDraft() {
    let report=formState.reportId?reports.find(r=>r.id===formState.reportId):null;
    if(!report){
      const c=cpcl.find(x=>x.id===formState.cpclId);
      const serial=String(reports.length+1).padStart(3,"0");
      report={id:"SIT-DEM-"+serial,cpclId:c.id,kelompok:c.kelompok,di:c.di,kecamatan:c.kecamatan,desa:c.desa,date:"30 Okt 2026",updatedISO:"2026-10-30",sentAt:null,period:"2026",status:"Draf",version:0,files:{bast:"",lpj:"",doc:""},feedback:"",history:[]};
      reports.unshift(report);formState.reportId=report.id;
    }
    Object.keys(formState.files).forEach(key=>report.files[key]=formState.files[key].name);
    report.date="30 Okt 2026";
    report.updatedISO="2026-10-30";
    if(!report.history.length)report.history.push({title:"Draf disimpan",detail:"Draf disimpan pada pratinjau lokal.",date:"30 Okt 2026"});
    else report.history.push({title:"Draf diperbarui",detail:"Perubahan tersimpan dalam pratinjau lokal.",date:"30 Okt 2026"});
    showToast("Draf tersimpan pada pratinjau browser. Tidak ada data dikirim ke server.");
    navigate("reports");
  }
  function submitReport() {
    let report=formState.reportId?reports.find(r=>r.id===formState.reportId):null;
    const keys=["bast","lpj","doc"];
    if(formState.revision){
      if(!keys.some(key=>!!formState.files[key])){showFormError("Pilih setidaknya satu dokumen yang diperbaiki sebelum mengirim ulang.");return;}
    } else {
      const missing=keys.filter(key=>!formState.files[key]&&!(report&&report.files[key]));
      if(missing.length){showFormError("Lengkapi BAST, LPJ, dan dokumentasi sebelum mengirim laporan.");return;}
    }
    if(!report){
      const c=cpcl.find(x=>x.id===formState.cpclId);
      const serial=String(reports.length+1).padStart(3,"0");
      report={id:"SIT-DEM-"+serial,cpclId:c.id,kelompok:c.kelompok,di:c.di,kecamatan:c.kecamatan,desa:c.desa,date:"30 Okt 2026",period:"2026",status:"Draf",version:0,files:{bast:"",lpj:"",doc:""},feedback:"",history:[]};
      reports.unshift(report);formState.reportId=report.id;
    }
    const nextFiles={...(report.files||{})};
    keys.forEach(key=>{if(formState.files[key])nextFiles[key]=formState.files[key].name;});
    report.files=nextFiles;
    report.version=(report.version||0)+1;
    report.status="Menunggu pemeriksaan";
    report.date="30 Okt 2026";
    report.updatedISO="2026-10-30";
    report.sentAt="2026-10-30";
    if(!Array.isArray(report.versions))report.versions=[];
    report.versions.push({
      number:report.version,
      files:{...report.files},
      status:report.status,
      date:"30 Okt 2026",
      feedback:""
    });
    report.feedback="";
    report.history.push({title:"Versi "+report.version+" dikirim",detail:"Laporan dikirim dan masuk antrean pemeriksaan pada pratinjau.",date:"30 Okt 2026"});
    showToast("Simulasi pengiriman berhasil. Laporan masuk Menunggu pemeriksaan.");
    navigate("reports");
  }
  function selectCpcl(id) {
    const existing=reports.find(r=>r.cpclId===id);
    if(existing){showToast("CPCL ini sudah memiliki paket laporan. Membuka paket yang sama.");openDetail(existing.id);}
    else startForm(id);
  }

  function openDetail(id) {
    const report=reports.find(r=>r.id===id);
    if(!report)return;
    currentReportId=id;
    const c=cpcl.find(x=>x.id===report.cpclId)||{};
    $("#detail-title").textContent=report.id;
    $("#detail-subtitle").textContent=report.kelompok+" · "+report.di;
    $("#detail-status").innerHTML=statusBadge(report.status);
    $("#detail-version").textContent="Versi "+Math.max(1,report.version);
    const versionList=Array.isArray(report.versions)?report.versions:[];
    $("#detail-version-history").innerHTML=versionList.length?versionList.slice().reverse().map(v=>{
      const names=["bast","lpj","doc"].map(key=>v.files&&v.files[key]?(key.toUpperCase()+": "+v.files[key]):null).filter(Boolean);
      return '<div class="version-entry"><div class="version-row"><span>Versi '+esc(v.number)+'</span>'+statusBadge(v.status)+'</div><small class="version-files">'+esc(names.join(" · ")||"Tidak ada dokumen tercatat")+'</small>'+(v.feedback?'<small class="version-feedback">'+esc(v.feedback)+'</small>':'')+'</div>';
    }).join(""):'<div class="empty-state">Belum ada versi yang dikirim.</div>';
    $("#detail-fields").innerHTML=[
      ["Kelompok Tani",report.kelompok],["Nama DI",report.di],["Kecamatan",report.kecamatan],["Desa",report.desa],["Periode",report.period],["ID CPCL",report.cpclId]
    ].map(([k,v])=>'<div class="detail-field"><small>'+k+'</small><strong>'+esc(v||"—")+'</strong></div>').join("");
    const docs=[["BAST","bast","PDF"],["LPJ","lpj","PDF"],["Dokumentasi","doc","PPTX"]];
    $("#detail-documents").innerHTML=docs.map(([label,key,type])=>'<div class="document-row"><span class="doc-icon">'+type+'</span><span class="doc-copy"><strong>'+esc(report.files[key]||"Belum diunggah")+'</strong><small>'+label+'</small></span><span class="doc-meta">'+(report.files[key]?"Tercatat":"Belum tersedia")+'</span></div>').join("");
    $("#detail-timeline").innerHTML=(report.history.length?report.history:[{title:"Draf dibuat",detail:"Belum ada pengiriman.",date:report.date}]).map((h,i)=>'<div class="timeline-entry"><span class="timeline-mark">'+(i+1)+'</span><div><strong>'+esc(h.title)+'</strong><small>'+esc(h.detail)+'</small></div><time>'+esc(h.date)+'</time></div>').join("");
    $("#detail-status-card").innerHTML='<div class="detail-status-large">'+statusBadge(report.status)+'</div><p class="aside-tip">'+(report.status==="Sesuai"?"Pemeriksaan dokumen telah selesai.":"Status akan berubah setelah tindakan yang sesuai dilakukan.")+'</p>';
    $("#detail-feedback").innerHTML=report.feedback?'<div class="feedback-box"><strong>Catatan Verifikator</strong><p>'+esc(report.feedback)+'</p></div>':"";
    let action="";
    if(report.status==="Tidak sesuai")action='<button class="btn btn-primary" data-action="revise" data-id="'+esc(report.id)+'">Perbaiki Laporan</button>';
    else if(report.status==="Draf")action='<button class="btn btn-primary" data-action="edit-draft" data-id="'+esc(report.id)+'">Lanjutkan Draf</button>';
    $("#detail-action").innerHTML=action;
    navigate("detail");
  }

  document.addEventListener("click",event=>{
    const button=event.target.closest("[data-view],[data-action]");
    if(!button)return;
    if(button.dataset.view){navigate(button.dataset.view);return;}
    const action=button.dataset.action,id=button.dataset.id;
    switch(action){
      case "go-cpcl":navigate("cpcl");break;
      case "go-reports":navigate("reports");break;
      case "reset-filters":
        ["report-search","report-province","report-group","report-di","report-district","report-village","status-filter","report-date-from","report-date-to"].forEach(id=>$("#"+id).value="");
        $("#report-sort").value="date-desc";renderReports();break;
      case "reset-all-filters":
        ["all-search","all-province","all-regency","all-status","all-date-from","all-date-to"].forEach(id=>$("#"+id).value="");
        $("#all-sort").value="date-desc";renderAllReports();break;
      case "reset-cpcl":$("#cpcl-search").value="";$("#cpcl-province").value="";$("#cpcl-regency").value="";$("#cpcl-district").value="";renderCpcl();break;
      case "detail-cpcl":openCpclDetail(id);break;
      case "close-cpcl-detail":$("#cpcl-detail-panel").hidden=true;break;
      case "choose-cpcl-from-detail":if(currentCpclId)selectCpcl(currentCpclId);break;
      case "select-cpcl":{
        selectCpcl(id);
        break;
      }
      case "detail":openDetail(id);break;
      case "save-draft":saveDraft();break;
      case "submit-report":submitReport();break;
      case "cancel-form":navigate("cpcl");break;
      case "back-to-reports":navigate("reports");break;
      case "revise":{const r=reports.find(x=>x.id===id);if(r)startForm(r.cpclId,r.id,true);break;}
      case "edit-draft":{const r=reports.find(x=>x.id===id);if(r)startForm(r.cpclId,r.id,false);break;}
      default:break;
    }
  });
  ["bast","lpj","doc"].forEach(key=>$("#file-"+key).addEventListener("change",()=>fileInputChanged(key)));
  ["report-search","report-province","report-group","report-di","report-district","report-village","status-filter","report-date-from","report-date-to","report-sort"].forEach(id=>{
    $("#"+id).addEventListener("input",renderReports);
    $("#"+id).addEventListener("change",renderReports);
  });
  ["cpcl-search","cpcl-province","cpcl-regency","cpcl-district"].forEach(id=>$("#"+id).addEventListener("input",renderCpcl));
  ["cpcl-province","cpcl-regency","cpcl-district"].forEach(id=>$("#"+id).addEventListener("change",renderCpcl));
  ["all-search","all-province","all-regency","all-status","all-date-from","all-date-to","all-sort"].forEach(id=>{
    $("#"+id).addEventListener("input",renderAllReports);
    $("#"+id).addEventListener("change",renderAllReports);
  });
  $("#notification-button").addEventListener("click",()=>showToast("Notifikasi pada pratinjau belum terhubung ke sistem."));
  renderDashboard();
  renderReports();
  renderCpcl();
  renderAllReports();
})();