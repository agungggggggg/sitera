(() => {
"use strict";
const $=(s,root=document)=>root.querySelector(s);
const esc=v=>String(v??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
const ACCOUNT_KEY="sitera-superadmin-accounts-v1",CPCL_KEY="sitera-superadmin-cpcl-v1",AUDIT_KEY="sitera-superadmin-audit-v1";
const FIELDS=[
 {key:"no",label:"No."},{key:"namaDI",label:"Nama DI"},{key:"kewenangan",label:"Kewenangan"},{key:"keterangan",label:"Keterangan"},
 {key:"provinsi",label:"Provinsi",identity:true},{key:"kabKota",label:"Kab_Kota",identity:true},{key:"kecamatan",label:"Kecamatan",identity:true},
 {key:"desa",label:"Desa/Kelurahan",identity:true},{key:"kelTani",label:"Kel_Tani",identity:true},{key:"namaKetua",label:"Nama Ketua"},
 {key:"realisasi",label:"Realisasi",numeric:true},{key:"luasHa",label:"Luas_ha",numeric:true},{key:"x",label:"X",numeric:true},{key:"y",label:"Y",numeric:true}
];
const seedCpcl=[
 {id:"CP-ADM-001",no:"1",namaDI:"DI Contoh Barat",kewenangan:"Kabupaten/Kota",keterangan:"Data ilustrasi",provinsi:"Provinsi Ilustrasi A",kabKota:"Kabupaten Demo Utara",kecamatan:"Kecamatan Utara",desa:"Desa Tirta",kelTani:"P3A Tirta Contoh",namaKetua:"Data ilustrasi",realisasi:"1",luasHa:"25.0",x:"105.0044",y:"-5.3749"},
 {id:"CP-ADM-002",no:"2",namaDI:"DI Sumber Demo",kewenangan:"Kabupaten/Kota",keterangan:"Data ilustrasi",provinsi:"Provinsi Ilustrasi A",kabKota:"Kabupaten Demo Selatan",kecamatan:"Kecamatan Selatan",desa:"Desa Sumber",kelTani:"Poktan Sumber Demo",namaKetua:"Data ilustrasi",realisasi:"1",luasHa:"18.5",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
 {id:"CP-ADM-003",no:"3",namaDI:"DI Karya Ilustrasi",kewenangan:"Kabupaten/Kota",keterangan:"Data ilustrasi",provinsi:"Provinsi Ilustrasi B",kabKota:"Kabupaten Contoh Barat",kecamatan:"Kecamatan Barat",desa:"Desa Karya",kelTani:"P3A Karya Ilustrasi",namaKetua:"Data ilustrasi",realisasi:"1",luasHa:"22.0",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
 {id:"CP-ADM-004",no:"4",namaDI:"DI Makmur Demo",kewenangan:"Kabupaten/Kota",keterangan:"Data ilustrasi",provinsi:"Provinsi Ilustrasi B",kabKota:"Kabupaten Contoh Timur",kecamatan:"Kecamatan Timur",desa:"Desa Makmur",kelTani:"Poktan Tani Makmur",namaKetua:"Data ilustrasi",realisasi:"1",luasHa:"14.0",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"},
 {id:"CP-ADM-005",no:"5",namaDI:"DI Harapan Contoh",kewenangan:"Kabupaten/Kota",keterangan:"Data ilustrasi",provinsi:"Provinsi Ilustrasi C",kabKota:"Kabupaten Demo Timur",kecamatan:"Kecamatan Timur",desa:"Desa Harapan",kelTani:"GP3A Harapan Tirta",namaKetua:"Data ilustrasi",realisasi:"1",luasHa:"30.0",x:"Belum dikonfirmasi",y:"Belum dikonfirmasi"}
];
const seedAccounts=[
 {id:"AK-001",role:"Pelapor",name:"PIC Kabupaten Demo Utara",contact:"Belum diisi",province:"Provinsi Ilustrasi A",regency:"Kabupaten Demo Utara",username:"kabupaten-demo-utara",status:"Aktif",verifiedBy:"Super Admin Demo",updatedAt:"30 Okt 2026"},
 {id:"AK-002",role:"Pelapor",name:"PIC Kabupaten Demo Selatan",contact:"Belum diisi",province:"Provinsi Ilustrasi A",regency:"Kabupaten Demo Selatan",username:"kabupaten-demo-selatan",status:"Menunggu verifikasi",verifiedBy:"—",updatedAt:"30 Okt 2026"},
 {id:"AK-003",role:"Pelapor",name:"PIC Kabupaten Contoh Barat",contact:"Belum diisi",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Barat",username:"kabupaten-contoh-barat",status:"Menunggu aktivasi",verifiedBy:"Super Admin Demo",updatedAt:"29 Okt 2026"},
 {id:"AK-004",role:"Verifikator",name:"Petugas Verifikator Demo",contact:"Belum diisi",province:"Pusat",regency:"—",username:"verifikator-demo",status:"Aktif",verifiedBy:"Super Admin Demo",updatedAt:"28 Okt 2026"},
 {id:"AK-005",role:"Pimpinan",name:"Pimpinan Demo",contact:"Belum diisi",province:"Pusat",regency:"—",username:"pimpinan-demo",status:"Aktif",verifiedBy:"Super Admin Demo",updatedAt:"28 Okt 2026"},
 {id:"AK-006",role:"Pelapor",name:"PIC Kabupaten Contoh Timur",contact:"Belum diisi",province:"Provinsi Ilustrasi B",regency:"Kabupaten Contoh Timur",username:"kabupaten-contoh-timur",status:"Nonaktif",verifiedBy:"Super Admin Demo",updatedAt:"27 Okt 2026"}
];
const load=(key,fallback)=>{try{const v=JSON.parse(localStorage.getItem(key)||"null");return Array.isArray(v)?v:fallback;}catch(_){return fallback;}};
let cpcl=load(CPCL_KEY,seedCpcl),accounts=load(ACCOUNT_KEY,seedAccounts),audit=load(AUDIT_KEY,[]);
let view="dashboard",toastTimer=null,previewAccounts=[];
let importState={file:null,fileName:"",workbook:null,sheets:[],sheetName:"",matrix:[],headers:[],mapping:{},rows:[],summary:null,stage:1,batchResult:null};
const save=()=>{try{localStorage.setItem(CPCL_KEY,JSON.stringify(cpcl));localStorage.setItem(ACCOUNT_KEY,JSON.stringify(accounts));localStorage.setItem(AUDIT_KEY,JSON.stringify(audit.slice(0,500)));}catch(_){}};
const toast=msg=>{const n=$("#toast");n.textContent=msg;n.classList.add("show");clearTimeout(toastTimer);toastTimer=setTimeout(()=>n.classList.remove("show"),3000);};
const todayLabel=()=>new Date().toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"});
const timeLabel=()=>new Date().toLocaleString("id-ID",{dateStyle:"medium",timeStyle:"short"});
const CPCL_DONUT_DATA={
 "Nasional":{total:1840,sesuai:500,belumSesuai:340,belumMelapor:1000},
 "Kabupaten Contoh Utara":{total:480,sesuai:130,belumSesuai:80,belumMelapor:270},
 "Kabupaten Contoh Barat":{total:510,sesuai:140,belumSesuai:90,belumMelapor:280},
 "Kabupaten Contoh Timur":{total:450,sesuai:120,belumSesuai:80,belumMelapor:250},
 "Kabupaten Contoh Selatan":{total:400,sesuai:110,belumSesuai:90,belumMelapor:200}
};
const CPCL_DONUT_COLORS={sesuai:"#0B3B70",belumSesuai:"#4E8CCB",belumMelapor:"#B9D2EA"};
const formatCount=value=>new Intl.NumberFormat("id-ID").format(value);
const norm=v=>String(v??"").normalize("NFKC").trim().replace(/\s+/g," ").toLocaleLowerCase("id-ID");
const slug=v=>norm(v).replace(/[^a-z0-9]+/g,"-").replace(/^-|-$/g,"");
const unique=v=>[...new Set(v.filter(Boolean))].sort((a,b)=>a.localeCompare(b,"id"));
const statusClass=s=>s==="Aktif"?"active":s==="Nonaktif"?"inactive":s==="Menunggu aktivasi"?"activation":"pending";
const accountBadge=s=>'<span class="account-status '+statusClass(s)+'">'+esc(s)+'</span>';
const cpclIdentity=r=>[r.provinsi,r.kabKota,r.kecamatan,r.desa,r.kelTani].map(norm).join("|");
const cpclAllEqual=(a,b)=>FIELDS.every(f=>norm(a[f.key])===norm(b[f.key]));
const auditAdd=(action,object,result="Berhasil",detail="")=>{audit.unshift({id:"AUD-"+Date.now()+"-"+Math.floor(Math.random()*1000),actor:"Super Admin (Demo)",time:timeLabel(),object,action,result,detail});save();};
const activePelaporFor=(province,regency)=>accounts.some(a=>a.role==="Pelapor"&&norm(a.province)===norm(province)&&norm(a.regency)===norm(regency)&&a.status==="Aktif");
function usernameFor(regency,province,fallback){
 const base=slug(regency||fallback)||"pengguna";
 let candidate=base;
 if(accounts.some(a=>norm(a.username)===norm(candidate)))candidate=base+"-"+(slug(province)||"pusat");
 let suffix=2;
 while(accounts.some(a=>norm(a.username)===norm(candidate))){candidate=base+"-"+(slug(province)||"pusat")+"-"+suffix;suffix++;}
 return candidate;
}
function updateUsernamePreview(){
 const role=$("#account-role").value,name=$("#account-name").value.trim(),province=$("#account-province").value.trim(),regency=$("#account-regency").value.trim();
 $("#account-username").value=usernameFor(role==="Pelapor"?regency:"",role==="Pelapor"?province:"Pusat",role==="Pelapor"?regency:name);
}
function setSelectOptions(id,values,placeholder,keep=""){
 const e=$("#"+id);if(!e)return;const value=values.includes(keep)?keep:"";
 e.innerHTML='<option value="">'+esc(placeholder)+'</option>'+values.map(v=>'<option value="'+esc(v)+'">'+esc(v)+'</option>').join("");e.value=value;
}
function setView(next){
 view=next;["dashboard","accounts","cpcl","imports","audit"].forEach(v=>$("#view-"+v).hidden=v!==next);
 document.querySelectorAll(".nav-item").forEach(b=>b.classList.toggle("active",b.dataset.view===next));
 const labels={
  dashboard:["Ringkasan","Ringkasan administrasi","Siapkan akun dan referensi CPCL untuk mendukung proses pelaporan."],
  accounts:["Manajemen Akun","Manajemen akun","Catat calon pengguna, verifikasi PIC, dan kelola status akses."],
  cpcl:["Data CPCL","Referensi CPCL","Kelola data kelompok tani dan identitas wilayah berdasarkan 14 kolom sumber."],
  imports:["Impor CPCL","Impor data CPCL","Unggah CSV/XLSX, petakan kolom, validasi, lalu konfirmasi penambahan data baru."],
  audit:["Audit Log","Audit log","Telusuri tindakan administratif tanpa mengubah catatan audit."]
 };
 $("#breadcrumb-current").textContent=labels[next][0];$("#page-title").textContent=labels[next][1];$("#page-description").textContent=labels[next][2];renderAll();window.scrollTo({top:0,behavior:"smooth"});
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
 $("#admin-metrics").innerHTML=[
  metric("Total akun",accounts.length,"Semua peran","♙"),
  metric("Akun Pelapor aktif",accounts.filter(a=>a.role==="Pelapor"&&a.status==="Aktif").length,"Satu akun aktif per kabupaten","✓","green"),
  metric("Menunggu tindakan",accounts.filter(a=>a.status==="Menunggu verifikasi"||a.status==="Menunggu aktivasi").length,"Verifikasi atau aktivasi","◷","amber"),
  metric("Total referensi CPCL",cpcl.length,"Baris contoh tersimpan","▤")
 ].join("");
 const pending=accounts.filter(a=>a.status==="Menunggu verifikasi"||a.status==="Menunggu aktivasi");
 $("#pending-accounts").innerHTML=pending.length?pending.slice(0,5).map(a=>'<div class="admin-list-row"><div class="admin-list-copy"><strong>'+esc(a.name)+'</strong><small>'+esc(a.username)+' · '+esc(a.regency||a.role)+'</small></div>'+accountBadge(a.status)+'</div>').join(""):'<div class="empty-state">Tidak ada akun yang memerlukan tindakan.</div>';
 $("#recent-audit").innerHTML=audit.length?audit.slice(0,5).map(a=>'<div class="admin-list-row"><div class="admin-list-copy"><strong>'+esc(a.action)+'</strong><small>'+esc(a.object)+' · '+esc(a.time)+'</small></div><span class="admin-list-meta">'+esc(a.result)+'</span></div>').join(""):'<div class="empty-state">Belum ada aktivitas administrasi pada sesi ini.</div>';
}
function renderAccounts(){
 const q=norm($("#account-search").value),status=$("#account-status-filter").value,role=$("#account-role-filter").value;
 const rows=accounts.filter(a=>(!q||[a.id,a.name,a.username,a.province,a.regency,a.contact].some(v=>norm(v).includes(q)))&&(!status||a.status===status)&&(!role||a.role===role));
 $("#accounts-table").innerHTML=rows.length?'<div class="table-wrap"><table class="data-table"><thead><tr><th>ID Akun</th><th>Peran</th><th>Pengguna / PIC</th><th>Wilayah</th><th>Username</th><th>Status</th><th>Aksi</th></tr></thead><tbody>'+rows.map(a=>{
  const location=a.role==="Pelapor"?a.province+" · "+a.regency:"Pusat";let action="";
  if(a.status==="Menunggu verifikasi")action='<button class="mini-action" data-action="verify-account" data-id="'+esc(a.id)+'">Verifikasi PIC</button>';
  else if(a.status==="Menunggu aktivasi")action='<button class="mini-action" data-action="activate-account" data-id="'+esc(a.id)+'">Simulasikan aktivasi</button>';
  else if(a.status==="Aktif")action='<button class="mini-action" data-action="reset-account" data-id="'+esc(a.id)+'">Reset akses</button><button class="mini-action danger" data-action="deactivate-account" data-id="'+esc(a.id)+'">Nonaktifkan</button>';
  else action='<button class="mini-action" data-action="reactivate-account" data-id="'+esc(a.id)+'">Verifikasi ulang</button>';
  return '<tr><td><span class="ticket">'+esc(a.id)+'</span></td><td>'+esc(a.role)+'</td><td><span class="cell-main">'+esc(a.name)+'</span><span class="cell-sub">'+esc(a.contact)+'</span></td><td>'+esc(location)+'</td><td>'+esc(a.username)+'</td><td>'+accountBadge(a.status)+'</td><td><span class="row-actions">'+action+'</span></td></tr>';
 }).join("")+'</tbody></table></div><div class="table-bottom">Menampilkan '+rows.length+' dari '+accounts.length+' akun · Data simulasi</div>':'<div class="filter-empty">Tidak ada akun yang cocok dengan filter.</div>';
}
function nextAccountId(){const max=accounts.reduce((m,a)=>Math.max(m,Number(a.id.replace(/\D/g,""))||0),0);return "AK-"+String(max+1).padStart(3,"0");}
function accountCreate(event){
 event.preventDefault();const role=$("#account-role").value,name=$("#account-name").value.trim(),contact=$("#account-contact").value.trim(),province=$("#account-province").value.trim(),regency=$("#account-regency").value.trim();
 if(!name){toast("Nama pengguna/PIC wajib diisi.");return;}
 if(role==="Pelapor"&&(!province||!regency)){toast("Provinsi dan Kabupaten/Kota wajib untuk akun Pelapor.");return;}
 if(role==="Pelapor"&&accounts.some(a=>a.role==="Pelapor"&&norm(a.province)===norm(province)&&norm(a.regency)===norm(regency))){
  const existing=accounts.find(a=>a.role==="Pelapor"&&norm(a.province)===norm(province)&&norm(a.regency)===norm(regency));
  toast("Pasangan wilayah sudah memiliki akun "+existing.id+". Kelola status akun tersebut, jangan membuat akun duplikat.");return;
 }
 const username=usernameFor(role==="Pelapor"?regency:"",role==="Pelapor"?province:"Pusat",role==="Pelapor"?regency:name);
 const id=nextAccountId();accounts.unshift({id,role,name,contact:contact||"Belum diisi",province:role==="Pelapor"?province:"Pusat",regency:role==="Pelapor"?regency:"—",username,status:"Menunggu verifikasi",verifiedBy:"—",updatedAt:todayLabel()});
 auditAdd("Tambah calon akun",id,"Berhasil","Status awal Menunggu verifikasi; kredensial belum diterbitkan.");
 save();$("#account-form").reset();$("#account-form-panel").hidden=true;renderAll();toast("Calon akun berhasil dicatat pada simulasi.");
}
function accountAction(action,id){
 const a=accounts.find(x=>x.id===id);if(!a)return;
 if(action==="verify-account"){a.status="Menunggu aktivasi";a.verifiedBy="Super Admin (Demo)";a.updatedAt=todayLabel();auditAdd("Verifikasi PIC",id,"Berhasil","Status menjadi Menunggu aktivasi.");}
 else if(action==="activate-account"){
  if(a.role==="Pelapor"&&activePelaporFor(a.province,a.regency)){auditAdd("Aktivasi akun",id,"Ditolak","Pasangan Provinsi + Kab_Kota sudah memiliki akun Pelapor aktif.");toast("Kabupaten/Kota pada provinsi ini sudah memiliki akun Pelapor aktif.");return;}
  a.status="Aktif";a.updatedAt=todayLabel();auditAdd("Aktivasi akun (simulasi)",id,"Berhasil","Simulasi hanya menandai status; penggantian sandi belum terjadi.");
 }else if(action==="reset-account"){a.status="Menunggu aktivasi";a.updatedAt=todayLabel();auditAdd("Permintaan reset akses (simulasi)",id,"Berhasil","Penerbitan sandi sementara dan pencabutan sesi belum dilakukan di backend.");}
 else if(action==="deactivate-account"){a.status="Nonaktif";a.updatedAt=todayLabel();auditAdd("Nonaktifkan akun (simulasi)",id,"Berhasil","Pencabutan seluruh sesi memerlukan backend.");}
 else if(action==="reactivate-account"){a.status="Menunggu verifikasi";a.updatedAt=todayLabel();a.verifiedBy="—";auditAdd("Ajukan verifikasi ulang akun",id,"Berhasil","Status kembali Menunggu verifikasi.");}
 save();renderAll();toast("Status akun diperbarui pada simulasi.");
}
function previewAccountsFromCpcl(){
 const pairs=new Map();cpcl.forEach(c=>{const key=norm(c.provinsi)+"|"+norm(c.kabKota);if(!pairs.has(key))pairs.set(key,{province:c.provinsi,regency:c.kabKota});});
 const used=new Set(accounts.map(a=>norm(a.username)));
 previewAccounts=[...pairs.values()].filter(p=>!accounts.some(a=>a.role==="Pelapor"&&norm(a.province)===norm(p.province)&&norm(a.regency)===norm(p.regency))).map(p=>{
  const base=slug(p.regency)||"pengguna";let candidate=base;
  if(used.has(norm(candidate)))candidate=base+"-"+(slug(p.province)||"provinsi");
  let suffix=2;while(used.has(norm(candidate))){candidate=base+"-"+(slug(p.province)||"provinsi")+"-"+suffix;suffix++;}
  used.add(norm(candidate));return {...p,username:candidate};
 });
 $("#account-preview-content").innerHTML=previewAccounts.length?'<div class="table-wrap"><table class="data-table"><thead><tr><th>Provinsi</th><th>Kabupaten/Kota</th><th>Username usulan</th></tr></thead><tbody>'+previewAccounts.map(p=>'<tr><td>'+esc(p.province)+'</td><td>'+esc(p.regency)+'</td><td>'+esc(p.username)+'</td></tr>').join("")+'</tbody></table></div><p class="admin-form-note">'+previewAccounts.length+' pasangan wilayah belum memiliki calon akun aktif/menunggu. Konfirmasi hanya membuat calon akun berstatus Menunggu verifikasi; tidak menerbitkan kredensial.</p>':'<div class="filter-empty">Tidak ada pasangan wilayah baru yang perlu dibuat.</div>';
 $("#account-preview-modal").hidden=false;
}
function confirmAccountsFromCpcl(){
 let made=0;previewAccounts.forEach(p=>{accounts.unshift({id:nextAccountId(),role:"Pelapor",name:"PIC belum diisi",contact:"Belum diisi",province:p.province,regency:p.regency,username:usernameFor(p.regency,p.province,p.regency),status:"Menunggu verifikasi",verifiedBy:"—",updatedAt:todayLabel()});made++;});
 if(made)auditAdd("Buat calon akun dari CPCL",made+" calon akun","Berhasil","Sumber: pasangan unik Provinsi + Kab_Kota.");
 $("#account-preview-modal").hidden=true;previewAccounts=[];save();renderAll();toast(made+" calon akun dibuat pada simulasi.");
}
function cpclFormBuild(){
 $("#cpcl-form-fields").innerHTML=FIELDS.map(f=>'<label>'+esc(f.label)+(f.identity?' <span class="required-mark">*</span>':'')+(f.numeric?' <small>Nilai numerik bila diisi</small>':'')+'<input id="cpcl-field-'+f.key+'" type="text" '+(f.identity?'required':'')+' placeholder="'+(f.identity?'Wajib untuk identitas sementara':'Opsional sesuai K07-06')+'"></label>').join("");
}
function renderCpcl(){
 const q=norm($("#cpcl-search").value),province=$("#cpcl-province-filter").value,regency=$("#cpcl-regency-filter").value;
 const provinces=unique(cpcl.map(c=>c.provinsi)),regencies=unique(cpcl.filter(c=>!province||c.provinsi===province).map(c=>c.kabKota));
 setSelectOptions("cpcl-province-filter",provinces,"Semua provinsi",province);setSelectOptions("cpcl-regency-filter",regencies,"Semua kabupaten/kota",regency);
 const rows=cpcl.filter(c=>(!q||FIELDS.some(f=>norm(c[f.key]).includes(q)))&&(!province||c.provinsi===province)&&(!regency||c.kabKota===regency));
 $("#cpcl-table").innerHTML=rows.length?'<div class="table-wrap"><table class="data-table"><thead><tr><th>No.</th><th>Nama DI</th><th>Kewenangan</th><th>Keterangan</th><th>Provinsi</th><th>Kab/Kota</th><th>Kecamatan</th><th>Desa/Kelurahan</th><th>Kel_Tani</th><th>Aksi</th></tr></thead><tbody>'+rows.map(c=>'<tr><td>'+esc(c.no)+'</td><td><span class="cell-main">'+esc(c.namaDI)+'</span><span class="cell-sub">'+esc(c.id)+'</span></td><td>'+esc(c.kewenangan)+'</td><td>'+esc(c.keterangan)+'</td><td>'+esc(c.provinsi)+'</td><td>'+esc(c.kabKota)+'</td><td>'+esc(c.kecamatan)+'</td><td>'+esc(c.desa)+'</td><td><span class="cell-main">'+esc(c.kelTani)+'</span></td><td><button class="action-link" data-action="detail-cpcl" data-id="'+esc(c.id)+'">Detail →</button></td></tr>').join("")+'</tbody></table></div><div class="table-bottom">Menampilkan '+rows.length+' dari '+cpcl.length+' data CPCL · Data simulasi</div>':'<div class="filter-empty">Tidak ada CPCL yang cocok dengan filter.</div>';
}
function detailCpcl(id){
 const c=cpcl.find(x=>x.id===id);if(!c)return;
 $("#cpcl-detail-title").textContent="Detail CPCL · "+id;$("#cpcl-detail-fields").innerHTML=FIELDS.map(f=>'<div class="detail-field"><small>'+esc(f.label)+'</small><strong>'+esc(c[f.key]||"Belum tersedia")+'</strong></div>').join("");
 $("#cpcl-detail-panel").hidden=false;$("#cpcl-detail-panel").scrollIntoView({behavior:"smooth",block:"nearest"});
}
function validNumeric(v){return v===""||/^[+-]?\d+(?:[.,]\d+)?$/.test(String(v).trim());}
function cpclFormSubmit(event){
 event.preventDefault();const row={};FIELDS.forEach(f=>row[f.key]=$("#cpcl-field-"+f.key).value.trim());
 const missing=FIELDS.filter(f=>f.identity&&!row[f.key]);if(missing.length){toast("Lengkapi identitas: "+missing.map(f=>f.label).join(", "));return;}
 const bad=FIELDS.filter(f=>f.numeric&&!validNumeric(row[f.key]));if(bad.length){toast("Format angka tidak valid pada: "+bad.map(f=>f.label).join(", "));return;}
 const same=cpcl.find(c=>cpclIdentity(c)===cpclIdentity(row));if(same){toast("Identitas kelompok sudah ada. Buka CPCL "+same.id+"; data lama tidak ditimpa.");detailCpcl(same.id);return;}
 row.id="CP-ADM-"+String(cpcl.length+1).padStart(4,"0");cpcl.push(row);auditAdd("Tambah CPCL manual",row.id,"Berhasil","Tambah baru; tidak ada CPCL lama yang ditimpa.");save();
 $("#cpcl-form").reset();$("#cpcl-form-panel").hidden=true;renderAll();toast("CPCL ditambahkan pada simulasi.");
}
function renderAudit(){
 const q=norm($("#audit-search").value),action=$("#audit-action-filter").value,result=$("#audit-result-filter").value;
 setSelectOptions("audit-action-filter",unique(audit.map(a=>a.action)),"Semua tindakan",action);
 const rows=audit.filter(a=>(!q||[a.actor,a.object,a.action,a.result,a.detail].some(v=>norm(v).includes(q)))&&(!action||a.action===action)&&(!result||a.result===result));
 $("#audit-table").innerHTML=rows.length?'<div class="table-wrap"><table class="data-table"><thead><tr><th>Waktu</th><th>Aktor</th><th>Tindakan</th><th>Objek</th><th>Hasil</th><th>Rincian</th></tr></thead><tbody>'+rows.map(a=>'<tr><td>'+esc(a.time)+'</td><td>'+esc(a.actor)+'</td><td><span class="cell-main">'+esc(a.action)+'</span></td><td>'+esc(a.object)+'</td><td><span class="audit-result '+(a.result==="Berhasil"?"ok":a.result==="Ditolak"?"denied":"failed")+'">'+esc(a.result)+'</span></td><td>'+esc(a.detail||"—")+'</td></tr>').join("")+'</tbody></table></div><div class="table-bottom">Menampilkan '+rows.length+' dari '+audit.length+' catatan · Hanya baca</div>':'<div class="filter-empty">Belum ada catatan audit yang sesuai.</div>';
}
function renderAll(){renderDashboard();renderAccounts();renderCpcl();renderAudit();}

/* CSV/XLSX is parsed locally in the browser. No file is sent to the server. */
function parseCsv(text){
 text=text.replace(/^\uFEFF/,"");const rows=[];let row=[],cell="",quoted=false;
 for(let i=0;i<text.length;i++){const ch=text[i];if(quoted){if(ch==='"'&&text[i+1]==='"'){cell+='"';i++;}else if(ch==='"')quoted=false;else cell+=ch;}
  else if(ch==='"')quoted=true;else if(ch===","){row.push(cell);cell="";}else if(ch==="\n"){row.push(cell);rows.push(row);row=[];cell="";}else if(ch!=="\r")cell+=ch;
 }
 if(cell.length||row.length){row.push(cell);rows.push(row);}while(rows.length&&rows[rows.length-1].every(v=>String(v).trim()===""))rows.pop();
 return rows;
}
function headerAutoMap(header){
 const h=norm(header).replace(/[._-]+/g," ").replace(/\s+/g," ").trim();
 const aliases={no:["no","nomor"],namaDI:["nama di","daerah irigasi"],kewenangan:["kewenangan"],keterangan:["keterangan"],provinsi:["provinsi"],kabKota:["kab kota","kabupaten kota","kabupaten/kota","kab_kota"],kecamatan:["kecamatan"],desa:["desa kelurahan","desa/kelurahan","desa"],kelTani:["kel tani","kel_tani","kelompok tani"],namaKetua:["nama ketua"],realisasi:["realisasi"],luasHa:["luas ha","luas_ha","luas"],x:["x"],y:["y"]};
 for(const f of FIELDS){if((aliases[f.key]||[]).some(v=>norm(v).replace(/[._-]+/g," ").replace(/\s+/g," ")===h))return f.key;}return "";
}
function setImportStage(stage){
 importState.stage=stage;["upload","mapping","preview","result"].forEach((name,i)=>$("#import-stage-"+name).hidden=i+1!==stage);
 document.querySelectorAll(".import-step").forEach(el=>{const n=Number(el.dataset.step);el.classList.toggle("active",n===stage);el.classList.toggle("done",n<stage);});
}
async function readImportFile(file){
 if(!file)return;const ext=file.name.split(".").pop().toLowerCase();if(ext!=="csv"&&ext!=="xlsx"){toast("Format harus CSV atau XLSX.");return;}
 importState={file,fileName:file.name,workbook:null,sheets:[],sheetName:"",matrix:[],headers:[],mapping:{},rows:[],summary:null,stage:2,batchResult:null};
 if(ext==="csv"){importState.matrix=parseCsv(await file.text());importState.sheetName="CSV";}
 else{
  if(!window.XLSX){toast("Pustaka pembaca XLSX belum tersedia. Muat ulang halaman dan coba lagi.");return;}
  const workbook=XLSX.read(await file.arrayBuffer(),{type:"array",cellFormula:false,cellHTML:false});
  importState.sheets=workbook.SheetNames.slice();if(!importState.sheets.length){toast("Workbook tidak memiliki sheet.");return;}
  importState.workbook=workbook;importState.sheetName=importState.sheets[0];
  $("#sheet-select-wrap").hidden=importState.sheets.length<2;
  $("#import-sheet").innerHTML=importState.sheets.map(s=>'<option value="'+esc(s)+'">'+esc(s)+'</option>').join("");
  importState.matrix=XLSX.utils.sheet_to_json(workbook.Sheets[importState.sheetName],{header:1,raw:false,defval:"",blankrows:true});
 }
 if(!importState.matrix.length){toast("File tidak memiliki baris untuk dibaca.");return;}
 $("#import-file-meta").textContent=importState.fileName+" · "+importState.matrix.length+" baris termasuk header · Pembacaan lokal di browser";
 setupMapping();setImportStage(2);
}
function loadSelectedSheet(name){
 if(!importState.workbook)return;importState.sheetName=name;
 importState.matrix=XLSX.utils.sheet_to_json(importState.workbook.Sheets[name],{header:1,raw:false,defval:"",blankrows:true});setupMapping();
}
function setupMapping(){
 const matrix=importState.matrix||[];importState.headers=(matrix[0]||[]).map(v=>String(v??"").trim());
 if(!importState.headers.length){toast("Header file tidak ditemukan.");return;}
 importState.mapping={};FIELDS.forEach(f=>{const idx=importState.headers.findIndex(h=>headerAutoMap(h)===f.key);importState.mapping[f.key]=idx>=0?String(idx):"";});
 $("#column-mapping").innerHTML=FIELDS.map(f=>{
  const options='<option value="">— Pilih header —</option>'+importState.headers.map((h,i)=>'<option value="'+i+'">'+esc(h||("Kolom "+(i+1)))+'</option>').join("");
  return '<div class="mapping-row"><strong>'+esc(f.label)+(f.identity?' *':'')+'</strong><select data-map-key="'+f.key+'" aria-label="Pemetaan '+esc(f.label)+'">'+options+'</select></div>';
 }).join("");
 document.querySelectorAll("[data-map-key]").forEach(s=>{s.value=importState.mapping[s.dataset.mapKey]||"";});
}
function rowToMapped(row,rowIndex){
 const data={};FIELDS.forEach(f=>{const idx=importState.mapping[f.key];data[f.key]=idx===""?"":String(row[Number(idx)]??"").trim();});data._row=rowIndex+2;return data;
}
function classifyRows(){
 const source=importState.matrix.slice(1),mapped=source.map((r,i)=>rowToMapped(r,i)).filter(r=>FIELDS.some(f=>r[f.key]!==""));
 const valid=[],classified=[];
 for(const r of mapped){
  const missing=FIELDS.filter(f=>f.identity&&!r[f.key]).map(f=>f.label+" wajib untuk identitas sementara");
  const bad=FIELDS.filter(f=>f.numeric&&r[f.key]!==""&&!validNumeric(r[f.key])).map(f=>f.label+" harus berupa angka");
  if(missing.length||bad.length)classified.push({...r,_status:"Tidak valid",_reason:[...missing,...bad].join("; ")});else valid.push(r);
 }
 const groups=new Map();valid.forEach(r=>{const key=cpclIdentity(r);if(!groups.has(key))groups.set(key,[]);groups.get(key).push(r);});
 for(const [identity,group] of groups){
  const existing=cpcl.find(c=>cpclIdentity(c)===identity);
  if(group.length>1&&group.some(r=>!cpclAllEqual(r,group[0]))){group.forEach(r=>classified.push({...r,_status:"Konflik",_reason:"Identitas sama muncul lebih dari sekali dengan isi berbeda; seluruh baris terkait ditahan."}));continue;}
  if(existing){const same=cpclAllEqual(existing,group[0]);group.forEach(r=>classified.push({...r,_status:same?"Duplikat":"Konflik",_reason:same?"Identitas dan isi sama dengan data CPCL yang sudah ada.":"Identitas sudah ada, tetapi isi berbeda. Impor tidak boleh menimpa data lama."}));continue;}
  group.forEach((r,i)=>classified.push({...r,_status:i===0?"Baru valid":"Duplikat",_reason:i===0?"Siap ditambahkan setelah konfirmasi.":"Baris identik berulang dalam file; hanya satu calon baris dipertahankan."}));
 }
 classified.sort((a,b)=>a._row-b._row);const summary={"Baru valid":0,"Duplikat":0,"Konflik":0,"Tidak valid":0};classified.forEach(r=>summary[r._status]++);
 importState.rows=classified;importState.summary=summary;return classified;
}
function validateImport(){
 const unmapped=FIELDS.filter(f=>importState.mapping[f.key]==="");if(unmapped.length){toast("Petakan semua 14 kolom terlebih dahulu: "+unmapped.map(f=>f.label).join(", "));return;}
 const rows=classifyRows(),s=importState.summary;
 $("#import-summary").innerHTML=[["Baru valid",s["Baru valid"],"new"],["Duplikat",s["Duplikat"],"duplicate"],["Konflik",s["Konflik"],"conflict"],["Tidak valid",s["Tidak valid"],"invalid"]].map(([label,value,cls])=>'<div class="import-summary-card '+cls+'"><span>'+label+'</span><strong>'+value+'</strong></div>').join("");
 $("#import-preview-table").innerHTML=rows.length?'<div class="table-wrap"><table class="data-table"><thead><tr><th>Baris</th><th>Kelompok tani</th><th>Wilayah</th><th>Status validasi</th><th>Alasan</th></tr></thead><tbody>'+rows.map(r=>'<tr><td>'+r._row+'</td><td><span class="cell-main">'+esc(r.kelTani)+'</span><span class="cell-sub">'+esc(r.namaDI)+'</span></td><td>'+esc(r.kabKota)+' · '+esc(r.provinsi)+'</td><td><span class="import-row-status '+slug(r._status)+'">'+esc(r._status)+'</span></td><td>'+esc(r._reason)+'</td></tr>').join("")+'</tbody></table></div><div class="table-bottom">Total '+rows.length+' baris data yang diperiksa. Tidak ada yang disimpan pada tahap ini.</div>':'<div class="filter-empty">Tidak ada baris data untuk diperiksa.</div>';
 $("#confirm-import-button").disabled=s["Baru valid"]===0;setImportStage(3);
}
function resetImport(){
 importState={file:null,fileName:"",workbook:null,sheets:[],sheetName:"",matrix:[],headers:[],mapping:{},rows:[],summary:null,stage:1,batchResult:null};
 $("#import-file").value="";$("#sheet-select-wrap").hidden=true;setImportStage(1);
}
function confirmImport(){
 const fresh=importState.rows.filter(r=>r._status==="Baru valid");if(!fresh.length){toast("Tidak ada baris baru valid untuk ditambahkan.");return;}
 let added=0;fresh.forEach(r=>{const obj={};FIELDS.forEach(f=>obj[f.key]=r[f.key]);obj.id="CP-IMP-"+Date.now()+"-"+String(added+1).padStart(2,"0");obj.importRow=r._row;cpcl.push(obj);added++;});
 const s=importState.summary,result={file:importState.fileName,sheet:importState.sheetName,added,duplicate:s["Duplikat"],conflict:s["Konflik"],invalid:s["Tidak valid"],time:timeLabel()};importState.batchResult=result;
 auditAdd("Impor CPCL",result.file,"Berhasil","Sheet: "+result.sheet+"; ditambah "+added+", duplikat "+result.duplicate+", konflik "+result.conflict+", tidak valid "+result.invalid+". Tidak ada data lama ditimpa.");
 save();
 $("#import-result-panel").innerHTML='<h3>Pratinjau penambahan selesai</h3><p>Batch simulasi berhasil menambahkan baris baru valid saja. File sumber tidak diunggah ke server; rincian batch disimpan sebagai log simulasi browser.</p><div class="import-result-grid"><span>Ditambahkan<strong>'+result.added+'</strong></span><span>Dilewati · duplikat<strong>'+result.duplicate+'</strong></span><span>Dilewati · konflik<strong>'+result.conflict+'</strong></span><span>Dilewati · tidak valid<strong>'+result.invalid+'</strong></span></div><p style="margin-top:12px">File: '+esc(result.file)+' · Sheet: '+esc(result.sheet)+' · Waktu: '+esc(result.time)+'</p>';
 setImportStage(4);renderAll();toast(added+" baris CPCL ditambahkan pada simulasi.");
}
function downloadTemplate(){
 const header=FIELDS.map(f=>f.label).join(",");
 const blob=new Blob([String.fromCharCode(0xFEFF)+header+String.fromCharCode(13,10)],{type:"text/csv;charset=utf-8"});
 const url=URL.createObjectURL(blob),a=document.createElement("a");a.href=url;a.download="template-impor-cpcl-sitera.csv";a.click();URL.revokeObjectURL(url);
}
function downloadTemplateXlsx(){
 if(!window.XLSX){toast("Pustaka XLSX belum tersedia.");return;}
 const workbook=XLSX.utils.book_new();
 const sheet=XLSX.utils.aoa_to_sheet([FIELDS.map(f=>f.label)]);
 XLSX.utils.book_append_sheet(workbook,sheet,"CPCL");
 XLSX.writeFile(workbook,"template-impor-cpcl-sitera.xlsx");
}
function resetAccountFilters(){["account-search","account-status-filter","account-role-filter"].forEach(id=>$("#"+id).value="");renderAccounts();}
function resetCpclFilters(){["cpcl-search","cpcl-province-filter","cpcl-regency-filter"].forEach(id=>$("#"+id).value="");renderCpcl();}
function resetAuditFilters(){["audit-search","audit-action-filter","audit-result-filter"].forEach(id=>$("#"+id).value="");renderAudit();}
function renderAll(){renderDashboard();renderAccounts();renderCpcl();renderAudit();}
document.addEventListener("click",event=>{
 const nav=event.target.closest("[data-view]");if(nav){setView(nav.dataset.view);return;}
 const b=event.target.closest("[data-action]");if(!b)return;
 const id=b.dataset.id;
 switch(b.dataset.action){
  case "go-accounts":setView("accounts");break;
  case "go-audit":setView("audit");break;
  case "toggle-account-form":$("#account-form-panel").hidden=!$("#account-form-panel").hidden;break;
  case "close-account-form":$("#account-form-panel").hidden=true;break;
  case "preview-accounts-from-cpcl":previewAccountsFromCpcl();break;
  case "close-account-preview":$("#account-preview-modal").hidden=true;break;
  case "confirm-accounts-from-cpcl":confirmAccountsFromCpcl();break;
  case "verify-account":case "activate-account":case "reset-account":case "deactivate-account":case "reactivate-account":accountAction(b.dataset.action,id);break;
  case "reset-account-filters":resetAccountFilters();break;
  case "toggle-cpcl-form":$("#cpcl-form-panel").hidden=!$("#cpcl-form-panel").hidden;break;
  case "close-cpcl-form":$("#cpcl-form-panel").hidden=true;break;
  case "detail-cpcl":detailCpcl(id);break;
  case "close-cpcl-detail":$("#cpcl-detail-panel").hidden=true;break;
  case "reset-cpcl-filters":resetCpclFilters();break;
  case "download-template":downloadTemplate();break;
  case "download-template-xlsx":downloadTemplateXlsx();break;
  case "cancel-import":resetImport();break;
  case "validate-import":validateImport();break;
  case "back-mapping":setImportStage(2);break;
  case "confirm-import":confirmImport();break;
  case "new-import":resetImport();break;
  case "reset-audit-filters":resetAuditFilters();break;
 }
});
$("#account-form").addEventListener("submit",accountCreate);
$("#cpcl-form").addEventListener("submit",cpclFormSubmit);
$("#account-role").addEventListener("change",()=>{
 const pelapor=$("#account-role").value==="Pelapor";
 $("#account-regency-wrap").hidden=!pelapor;$("#account-province").disabled=!pelapor;$("#account-regency").disabled=!pelapor;
 updateUsernamePreview();
});
["account-name","account-province","account-regency"].forEach(id=>$("#"+id).addEventListener("input",updateUsernamePreview));
["account-search","account-status-filter","account-role-filter"].forEach(id=>{ $("#"+id).addEventListener("input",renderAccounts);$("#"+id).addEventListener("change",renderAccounts);});
["cpcl-search","cpcl-province-filter","cpcl-regency-filter"].forEach(id=>{ $("#"+id).addEventListener("input",renderCpcl);$("#"+id).addEventListener("change",renderCpcl);});
["audit-search","audit-action-filter","audit-result-filter"].forEach(id=>{ $("#"+id).addEventListener("input",renderAudit);$("#"+id).addEventListener("change",renderAudit);});
$("#import-file").addEventListener("change",async e=>{try{await readImportFile(e.target.files&&e.target.files[0]);}catch(err){console.error(err);toast("File tidak dapat dibaca. Periksa format dan struktur file.");}});
$("#import-sheet").addEventListener("change",e=>{try{loadSelectedSheet(e.target.value);}catch(err){console.error(err);toast("Sheet tidak dapat dibaca.");}});
$("#column-mapping").addEventListener("change",e=>{const s=e.target.closest("[data-map-key]");if(s)importState.mapping[s.dataset.mapKey]=s.value;});
$("#cpcl-chart-regency").addEventListener("change",renderCpclDonut);
$("#notification-button").addEventListener("click",()=>toast("Notifikasi belum termasuk fungsi inti administrasi pada pratinjau."));
$("#account-preview-modal").addEventListener("click",e=>{if(e.target.id==="account-preview-modal")$("#account-preview-modal").hidden=true;});
function init(){
 cpclFormBuild();updateUsernamePreview();
 if(!localStorage.getItem(AUDIT_KEY))auditAdd("Buka area Super Admin","Area administrasi","Berhasil","Pratinjau demo dimulai; autentikasi belum aktif.");
 save();renderAll();setImportStage(1);
}
init();
})();