(()=> {
  "use strict";
  const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)],API=new URL("api/save-card.php",location.href.split("?")[0]).href,AUTH=new URL("api/auth.php",location.href.split("?")[0]).href,MEDIA=new URL("api/media.php",location.href.split("?")[0]).href,KEY="lavDigitalCards_v3";
  const HOME="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1100&q=80",CAR="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1100&q=80";
  const L=[["cinematic","Portada cinematográfica","universal","Foto inmersiva"],["split","Editorial dividido","property","Dos columnas"],["luxury","Lujo oscuro","universal","Detalles premium"],["minimal","Minimalista limpio","property","Espacio y claridad"],["panoramic","Panorámico","property","Foto amplia"],["editorial","Revista editorial","property","Composición de revista"],["mosaic","Mosaico fotográfico","universal","Galería protagonista"],["gallery","Galería protagonista","universal","Exploración visual"],["sidebar","Panel lateral","property","Ficha lateral"],["compact","Ficha para compartir","universal","Compacta"],["architecture","Arquitectónico","property","Líneas premium"],["geometric","Geométrico contemporáneo","universal","Composición asimétrica"],["warm","Hogar cálido","property","Estilo acogedor"],["investment","Inversión inmobiliaria","property","Precio y datos clave"],["impact","Alto impacto","universal","Titulares contundentes"],["auto-night","Automotriz nocturno","vehicle","Estética nocturna"],["auto-editorial","Automotriz editorial","vehicle","Ficha automotriz"],["sport","Deportivo dinámico","vehicle","Diseño deportivo"],["dealer","Concesionaria","vehicle","Ficha técnica"],["universal","Comercial universal","universal","Uso general"]];
  const S=[["signature","Signature"],["editorial","Editorial"],["colorpop","Color Pop"],["neon","Neon Studio"],["luxury","Luxury"]];
  const P=[
  ["classic","Obsidiana dorada", {
    primary:"#c9a96e",background:"#10131c",text:"#f8f7f4",accent:"#e4c58a",gradient:"none"
  }],
  ["sage","Bosque de lujo", {
    primary:"#b4c7a1",background:"#182b26",text:"#f6f3e9",accent:"#d8e5c8",gradient:"none"
  }],
  ["ocean","Océano eléctrico", {
    primary:"#2dd4bf",background:"#071c2c",text:"#effbff",accent:"#8cefe0",gradient:"ocean"
  }],
  ["burgundy","Borgoña", {
    primary:"#dca1a9",background:"#2b101a",text:"#fff3f3",accent:"#f0c6c9",gradient:"none"
  }],
  ["violet","Aurora violeta", {
    primary:"#8b7cff",background:"#15102d",text:"#ffffff",accent:"#c6bbff",gradient:"aurora"
  }],
  ["terracotta","Terracota", {
    primary:"#e58e68",background:"#35201b",text:"#fff3e9",accent:"#f5b08f",gradient:"none"
  }],
  ["emerald","Esmeralda", {
    primary:"#34d399",background:"#06291f",text:"#f0fff8",accent:"#9af0c9",gradient:"none"
  }],
  ["rose","Rose gold", {
    primary:"#df9fa6",background:"#24151b",text:"#fff7f7",accent:"#f2c7be",gradient:"none"
  }],
  ["carbon","Carbono rojo", {
    primary:"#ff554d",background:"#171717",text:"#f5f5f5",accent:"#ff948b",gradient:"none"
  }],
  ["midnight","Azul medianoche", {
    primary:"#6ba8ff",background:"#07162e",text:"#f5f8ff",accent:"#b4d4ff",gradient:"none"
  }]
  ];
  const T=L.flatMap((l,i)=>S.flatMap((s,j)=>P.map((p,k)=>( {
    id:k===0?l[0]+"-"+s[0]:l[0]+"-"+s[0]+"-"+p[0],layout:l[0],name:l[1],category:l[2],desc:l[3],skin:s[0],skinName:s[1],palette:p[0],paletteName:p[1],colors:p[2],number:(i*5+j)*P.length+k+1
  }))));
  const B= {
    id:null,clientName:"",contactName:"",kind:"property",status:"draft",operation:"Venta",title:"",price:"",currency:"MXN",location:"",description:"",propertyType:"Casa",landArea:"",builtArea:"",bedrooms:"",bathrooms:"",parking:"",amenities:"",vehicleType:"Automóvil",make:"",model:"",version:"",year:"",mileage:"",transmission:"Automática",fuel:"Gasolina",phone:"",whatsapp:"",email:"",facebook:"",instagram:"",mapsUrl:"",logo:"",cover:"",gallery:[],backgroundImage:"",templateId:"cinematic-signature",galleryStyle:"slide",theme: {
      primary:"#c9a96e",background:"#10131c",text:"#f8f7f4",accent:"#e4c58a",gradient:"none",font:"sans",radius:18,imageHeight:340,overlay:42
    },createdAt:null,updatedAt:null
  };
  let cards=[],cur=null,gidx=0,templatePage=1;
  const esc=v=>String(v??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c])),copy=o=>JSON.parse(JSON.stringify(o)),img=c=>c.cover||(c.kind==="vehicle"?CAR:HOME);
function safe(v){if(!v)return"";try{let u=new URL(v,location.href);return ["http:","https:"].includes(u.protocol)?u.href:""}catch{return""}}
function toast(m,t="success"){let e=document.createElement("div");e.className="toast "+t;e.textContent=m;$("#toastRegion").append(e);setTimeout(()=>e.remove(),4200)}
function stat(m,err=false){$("#saveStatus").innerHTML=(err?'<i class="fa-solid fa-triangle-exclamation"></i> ':'<i class="fa-solid fa-cloud"></i> ')+esc(m)}
async function get(p={}){let u=new URL(API);Object.entries(p).forEach(([k,v])=>u.searchParams.set(k,v));let r=await fetch(u,{cache:"no-store"});if(!r.ok)throw Error("Servidor "+r.status);let d=await r.json();if(d.success===false)throw Error(d.message);return d}
async function post(p){let r=await fetch(API,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(p)});if(!r.ok)throw Error("Servidor "+r.status);let d=await r.json();if(d.success===false)throw Error(d.message);return d}
function money(c){let n=Number(String(c.price||"").replace(/[^\d.-]/g,""));if(!c.price||!Number.isFinite(n))return"";try{return new Intl.NumberFormat("es-MX",{style:"currency",currency:c.currency||"MXN",maximumFractionDigits:0}).format(n)}catch{return"$"+n.toLocaleString("es-MX")}}
function grad(t){let b=t.background,p=t.primary;return({none:"linear-gradient(0deg,"+b+","+b+")",diagonal:"linear-gradient(135deg,"+b+","+p+" 180%)",radial:"radial-gradient(ellipse at 80% 0%,"+p+","+b+" 72%)",sunset:"linear-gradient(135deg,#24142e,#9d435b,#f1a86d)",ocean:"linear-gradient(135deg,#071c2c,#155b6d,#65c9b7)",aurora:"linear-gradient(135deg,#11152d,#3d326c,#176b72)"})[t.gradient]||"linear-gradient(0deg,"+b+","+b+")"}
function css(c){let t={...B.theme,...c.theme},f={sans:'"DM Sans",sans-serif',serif:'"Playfair Display",serif',condensed:'"Arial Narrow",sans-serif'}[t.font];return '--card-bg:'+t.background+';--card-primary:'+t.primary+';--card-text:'+t.text+';--card-accent:'+t.accent+';--card-radius:'+t.radius+'px;--cover-height:'+t.imageHeight+'px;--shade:'+t.overlay+'%;--card-font:'+(f||f)+';--card-gradient:'+grad(t)+';--custom-bg-image:'+(c.backgroundImage?'url('+c.backgroundImage+')':'none')}
function facts(c){return(c.kind==="vehicle"?[[c.year,"Año","calendar"],[c.mileage,"Kilometraje","gauge-high"],[c.transmission,"Transmisión","gears"],[c.fuel,"Combustible","gas-pump"],[c.make,"Marca","car"],[c.version,"Versión","tag"]]:c.kind==="property"?[[c.landArea,"Terreno","vector-square","m²"],[c.builtArea,"Construcción","ruler-combined","m²"],[c.bedrooms,"Recámaras","bed"],[c.bathrooms,"Baños","bath"],[c.parking,"Estacionamiento","car"]]:[]).filter(x=>x[0]!==""&&x[0]!=null)}
function contact(c){let a=[],w=String(c.whatsapp||c.phone||"").replace(/\D/g,"");if(w)a.push('<a href="https://wa.me/'+esc(w)+'" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>');if(c.phone)a.push('<a class="secondary" href="tel:'+esc(c.phone.replace(/[^+\d]/g,""))+'"><i class="fa-solid fa-phone"></i> Llamar</a>');if(c.email)a.push('<a class="secondary" href="mailto:'+esc(c.email)+'"><i class="fa-regular fa-envelope"></i> Correo</a>');let m=safe(c.mapsUrl)|| (c.location?"https://www.google.com/maps/search/?api=1&query="+encodeURIComponent(c.location):"");if(m)a.push('<a class="secondary" href="'+esc(m)+'" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i> Ubicación</a>');for(let [k,ic] of [["facebook","facebook-f"],["instagram","instagram"]])if(safe(c[k]))a.push('<a class="secondary" href="'+esc(safe(c[k]))+'" target="_blank" rel="noopener"><i class="fa-brands fa-'+ic+'"></i> '+k+'</a>');if(c.id)a.push('<button class="secondary" data-save-contact><i class="fa-solid fa-address-card"></i> Guardar contacto</button><button class="secondary" data-share-card><i class="fa-solid fa-share-nodes"></i> Compartir tarjeta</button><button class="secondary" data-show-qr><i class="fa-solid fa-qrcode"></i> QR</button>');return a.length?'<div class="dc-contact">'+a.join("")+'</div>':""}
  function gallery(c,i=0,thumb=false) {
    let p=c.gallery||[];if(!p.length||thumb)return"";i=(i+p.length)%p.length;return'<div class="dc-section-label">GALERÍA · '+p.length+' FOTOGRAFÍAS</div><div class="dc-gallery-viewer effect-'+esc(c.galleryStyle||"slide")+'"><img src="'+esc(p[i])+'" alt="Fotografía '+(i+1)+'" data-lightbox-index="'+i+'"><div class="dc-gallery-controls"><button data-gallery-prev aria-label="Anterior"><i class="fa-solid fa-arrow-left"></i></button><span class="dc-gallery-count">'+(i+1)+' / '+p.length+'</span><button data-gallery-next aria-label="Siguiente"><i class="fa-solid fa-arrow-right"></i></button></div></div><div class="dc-gallery">'+p.slice(0,6).map((x,j)=>'<button class="dc-gallery-item" data-gallery-go="'+j+'"><img src="'+esc(x)+'" alt="Foto '+(j+1)+'"></button>').join("")+'</div>'
  }
  function card(c,o= {
  }) {
    let th=!!o.thumb,pt=!!o.publicMode,t=T.find(x=>x.id===c.templateId)||T[0],kind=c.kind==="vehicle"?(c.vehicleType||"Vehículo"):c.kind==="profile"?"Perfil comercial":c.propertyType||"Propiedad",p=money(c),f=facts(c),cat=o.catalog||[],catalog=c.kind==="profile"&&cat.length?'<div class="dc-section-label">ANUNCIOS DISPONIBLES</div><div class="dc-gallery">'+cat.map(x=>'<a class="dc-gallery-item" href="?card='+encodeURIComponent(x.id)+'"><img src="'+esc(img(x))+'" alt="'+esc(x.title)+'"></a>').join("")+'</div>':"",title=c.title||c.clientName||"Anuncio destacado",kicker=c.kind==="vehicle"?[c.make,c.model,c.year].filter(Boolean).join(" · "):c.kind==="property"?[c.operation,kind].join(" · "):"PRESENTACIÓN COMERCIAL";return'<article class="digital-card layout-'+t.layout+' skin-'+t.skin+(pt?" public-card":"")+'" data-card-id="'+esc(c.id||"preview")+'" style="'+css(c)+'"><div class="dc-cover" style="background-image:linear-gradient(180deg,#0001,#0000),url(&quot;'+esc(th?(c.kind==="vehicle"?CAR:HOME):img(c))+'&quot;)"><div class="dc-brand">'+(c.logo&&!th?'<img class="dc-logo" src="'+esc(c.logo)+'" alt="Logo">':"")+'<span>'+esc((c.clientName||"LAV DIGITAL").toUpperCase())+'</span></div><span class="dc-kind">'+esc(kind)+'</span><div class="dc-cover-copy"><div class="dc-kicker">'+esc(kicker)+'</div><h2 class="dc-title">'+esc(title)+'</h2>'+(p?'<div class="dc-price">'+esc(p)+(c.operation==="Renta"&&c.kind==="property"?" <small>/ mes</small>":"")+'</div>':"")+'<div class="dc-location">'+(c.location?'<i class="fa-solid fa-location-dot"></i> '+esc(c.location):"")+'</div></div>'+(th?"":'<button class="dc-cover-open" data-lightbox-index="-1" aria-label="Ampliar portada"></button>')+'</div><div class="dc-content">'+(c.description?'<p class="dc-description">'+esc(c.description)+'</p>':"")+(f.length?'<div class="dc-specs">'+f.slice(0,6).map(x=>'<div class="dc-spec"><i class="fa-solid fa-'+x[2]+'"></i><strong>'+esc(x[0]+(x[3]?" "+x[3]:""))+'</strong><span>'+esc(x[1])+'</span></div>').join("")+'</div>':"")+(c.amenities?'<div class="dc-section-label">CARACTERÍSTICAS</div><p class="dc-description">'+esc(c.amenities)+'</p>':"")+gallery(c,o.galleryIndex||0,th)+catalog+'<div class="dc-section-label">'+(c.contactName?"CONTACTO · "+esc(c.contactName):"CONTACTO DIRECTO")+'</div>'+contact(c)+'<div class="dc-footer">PRESENTADO POR '+esc((c.clientName||"LAV DIGITAL").toUpperCase())+'</div></div></article>'
  }
  function date(v) {
    if(!v)return"ahora";let d=new Date(v);return isNaN(d)?"":new Intl.DateTimeFormat("es-MX", {
      day:"2-digit",month:"short",year:"numeric"
    }).format(d)
  }
  function dashboard() {
    let q=$("#searchInput").value.toLowerCase().trim(),ty=$("#typeFilter").value,st=$("#statusFilter").value,cl=$("#clientFilter").value,list=cards.filter(c=>(!q||[c.title,c.clientName,c.location,c.make,c.model].join(" ").toLowerCase().includes(q))&&(ty==="all"||c.kind===ty)&&(st==="all"||c.status===st)&&(cl==="all"||c.clientName===cl));$("#statTotal").textContent=cards.length;$("#statPublished").textContent=cards.filter(c=>c.status==="published").length;$("#statClients").textContent=new Set(cards.map(c=>c.clientName).filter(Boolean)).size;let names=[...new Set(cards.map(c=>c.clientName).filter(Boolean))].sort(),cf=$("#clientFilter"),old=cf.value;cf.innerHTML='<option value="all">Todos los clientes</option>'+names.map(n=>'<option value="'+esc(n)+'">'+esc(n)+'</option>').join("");cf.value=names.includes(old)?old:"all";$("#inventoryGrid").innerHTML=list.map(c=>'<article class="inventory-card"><div class="inventory-cover" style="background-image:url(&quot;'+esc(img(c))+'&quot;)"><span class="inventory-tag">'+esc(c.kind==="vehicle"?"Vehículo":c.kind==="profile"?"Perfil":c.propertyType||"Bienes raíces")+'</span><span class="inventory-status '+esc(c.status||"draft")+'">'+(c.status==="published"?"Publicado":c.status==="archived"?"Archivado":"Borrador")+'</span></div><div class="inventory-card-body"><div class="inventory-price">'+esc(money(c)||"Precio por consultar")+'</div><div class="inventory-title">'+esc(c.title||"Anuncio sin título")+'</div><div class="inventory-meta"><span><i class="fa-regular fa-building"></i> '+esc(c.clientName||"Sin cliente")+'</span>'+(c.location?'<span><i class="fa-solid fa-location-dot"></i> '+esc(c.location)+'</span>':"")+'</div><div class="inventory-client">Actualizado: '+esc(date(c.updatedAt||c.createdAt))+'</div><div class="inventory-actions"><button class="button button-outline" data-action="edit" data-id="'+esc(c.id)+'">Editar</button><button class="button button-outline" data-action="duplicate" data-id="'+esc(c.id)+'">Copiar</button><button class="button button-outline" data-action="link" data-id="'+esc(c.id)+'">Enlace</button><button class="button button-outline" data-action="more" data-id="'+esc(c.id)+'">•••</button></div><div class="inventory-actions hidden" id="more-'+esc(c.id)+'"><button class="button button-outline" data-action="toggle" data-id="'+esc(c.id)+'">'+(c.status==="published"?"Despublicar":"Publicar")+'</button><button class="button button-outline" data-action="archive" data-id="'+esc(c.id)+'">Archivar</button><button class="button button-outline" data-action="delete" data-id="'+esc(c.id)+'">Eliminar</button></div></div></article>').join("");$("#emptyState").classList.toggle("hidden",!!list.length);$("#inventoryGrid").classList.toggle("hidden",!list.length)
  }
  function show(v) {
    $("#dashboardView").classList.toggle("hidden",v!=="dash");$("#editorView").classList.toggle("hidden",v!=="edit");$("#publicView").classList.toggle("hidden",v!=="public");scrollTo(0,0)
  }
  function fill(c) {
    let keys=["clientName","contactName","operation","price","currency","location","description","propertyType","landArea","builtArea","bedrooms","bathrooms","parking","amenities","vehicleType","make","model","version","year","mileage","transmission","fuel","phone","whatsapp","email","facebook","instagram","mapsUrl"];keys.forEach(k=> {
      if($("#"+k))$("#"+k).value=c[k]??""
    });$("#listingTitle").value=c.title||"";$("#listingType").value=c.kind||"property";$("#propertyFields").classList.toggle("hidden",c.kind!=="property");$("#vehicleFields").classList.toggle("hidden",c.kind!=="vehicle");$("#operationField").classList.toggle("hidden",c.kind!=="property");let t= {
      ...B.theme,...c.theme
    };for(let k of ["primary","background","text","accent"])$("#custom"+k[0].toUpperCase()+k.slice(1)).value=t[k];$("#gradientStyle").value=t.gradient;$("#fontStyle").value=t.font;$("#cardRadius").value=t.radius;$("#imageHeight").value=t.imageHeight;$("#overlayStrength").value=t.overlay;$$('input[name="galleryStyle"]').forEach(x=>x.checked=x.value===(c.galleryStyle||"slide"));$("#coverPreview").innerHTML=c.cover?'<img src="'+esc(c.cover)+'">':"";$("#logoPreview").innerHTML=c.logo?'<img src="'+esc(c.logo)+'">':"";$("#removeLogo").classList.toggle("hidden",!c.logo);$("#backgroundPreview").innerHTML=c.backgroundImage?'<img src="'+esc(c.backgroundImage)+'">':"";galleryManager()
  }
  function read() {
    if(!cur)return;for(let k of ["clientName","contactName","operation","price","currency","location","description","propertyType","landArea","builtArea","bedrooms","bathrooms","parking","amenities","vehicleType","make","model","version","year","mileage","transmission","fuel","phone","whatsapp","email","facebook","instagram","mapsUrl"])if($("#"+k))cur[k]=$("#"+k).value.trim();cur.title=$("#listingTitle").value.trim();cur.kind=$("#listingType").value;cur.galleryStyle=$('input[name="galleryStyle"]:checked')?.value||"slide";cur.theme= {
      primary:$("#customPrimary").value,background:$("#customBackground").value,text:$("#customText").value,accent:$("#customAccent").value,gradient:$("#gradientStyle").value,font:$("#fontStyle").value,radius:+$("#cardRadius").value,imageHeight:+$("#imageHeight").value,overlay:+$("#overlayStrength").value
    };return cur
  }
  function newCard() {
    cur=copy(B);gidx=0;fill(cur);$("#editorModeLabel").textContent="Nuevo proyecto";$("#editorIdLabel").textContent="Borrador sin guardar";show("edit");step("details");renderTemplates();preview();stat("Cambios pendientes")
  }
  function edit(id) {
    let c=cards.find(x=>String(x.id)===String(id));if(!c)return;cur= {
      ...copy(B),...c,theme: {
        ...copy(B.theme),...(c.theme|| {
        })
      }
    };gidx=0;fill(cur);$("#editorModeLabel").textContent=cur.title||"Editar proyecto";$("#editorIdLabel").textContent=(cur.status==="published"?"Publicado":"Borrador")+" · "+cur.id;show("edit");step("details");renderTemplates();preview()
  }
  function step(n) {
    $$(".step-tab").forEach(b=>b.classList.toggle("active",b.dataset.step===n));$$(".editor-step").forEach(s=>s.classList.toggle("active",s.id==="step-"+n));if(n==="design")renderTemplates()
  }
  function preview() {
    if(!cur)return;let c=read(),t=T.find(x=>x.id===c.templateId)||T[0];$("#previewCard").className="digital-card preview-card layout-"+t.layout+" skin-"+t.skin;$("#previewCard").innerHTML=(c.title||c.cover||c.clientName)?card(c, {
      galleryIndex:gidx
    }):'<div class="preview-placeholder"><i class="fa-regular fa-images"></i><h3>Tu diseño aparecerá aquí</h3><p>Completa los datos y carga una fotografía para comenzar.</p></div>';$("#previewCard").style.cssText=(c.title||c.cover||c.clientName)?css(c):"";$("#previewDesignName").textContent=t.name+" · "+t.skinName;$("#editorModeLabel").textContent=c.title||"Nuevo proyecto"
  }
  function renderTemplates() {
    if(!cur)return;let q=$("#templateSearch").value.toLowerCase(),cat=$("#templateCategory").value,sk=$("#templateSkin").value,list=T.filter(t=>(!q||(t.name+" "+t.skinName+" "+t.desc+" "+t.paletteName).toLowerCase().includes(q))&&(cat==="all"||t.category===cat)&&(sk==="all"||t.skin===sk)),limit=Math.min(list.length,templatePage*60);$("#templateResults").textContent=list.length.toLocaleString("es-MX")+" diseños disponibles · mostrando "+limit;$("#templateGrid").innerHTML=list.slice(0,limit).map(t=> {
      let c= {
        ...cur,theme: {
          ...cur.theme,...t.colors
        },templateId:t.id,cover:"",gallery:[],title:"Residencia contemporánea",clientName:"LAV DIGITAL",price:"4850000",location:"Cuernavaca, Morelos",description:"",phone:"",whatsapp:"",email:"",facebook:"",instagram:"",mapsUrl:"",logo:""
      };return'<button class="template-item '+(cur.templateId===t.id?"selected":"")+'" data-template="'+t.id+'"><div class="template-thumb">'+card(c, {
        thumb:true
      })+'</div><div class="template-meta"><strong><span class="template-number">'+String(t.number).padStart(4,"0")+'</span>'+esc(t.name)+'</strong><small>'+esc(t.skinName)+' · '+esc(t.paletteName)+'</small></div></button>'
    }).join("");let more=$("#loadMoreTemplates");if(more) {
      more.classList.toggle("hidden",limit>=list.length);more.textContent="Ver más diseños ("+(list.length-limit)+" restantes)"
    }
  }
  function galleryManager() {
    if(!cur)return;$("#galleryCount").textContent=cur.gallery.length+" fotos";$("#galleryManager").innerHTML=cur.gallery.map((p,i)=>'<div class="gallery-thumb" draggable="true" data-photo-index="'+i+'"><img src="'+esc(p)+'"><button type="button" data-remove-photo="'+i+'">×</button>'+(i===0?'<span class="cover-label">Foto 1</span>':"")+'</div>').join("")
  }
  async function readImage(f,max=1500,q=.75) {
    if(!f||!f.type.startsWith("image/"))throw Error("Elige un archivo de imagen válido.");if(f.size>25*1024*1024)throw Error("La imagen supera 25 MB.");try {
      let b=await createImageBitmap(f),s=Math.min(1,max/Math.max(b.width,b.height)),c=document.createElement("canvas");c.width=Math.round(b.width*s);c.height=Math.round(b.height*s);c.getContext("2d").drawImage(b,0,0,c.width,c.height);b.close?.();return c.toDataURL("image/jpeg",q)
    }
    catch {
      return await new Promise((res,rej)=> {
        let r=new FileReader();r.onload=()=>res(r.result);r.onerror=()=>rej(Error("No se pudo leer la imagen."));r.readAsDataURL(f)
      })
    }
  }
  async function uploadImage(data,name) {
    let parts=data.split(","),mime=(parts[0].match(/:(.*?);/)||[])[1]||"image/jpeg",bin=atob(parts[1]||""),bytes=new Uint8Array(bin.length);for(let i=0;i<bin.length;i++)bytes[i]=bin.charCodeAt(i);let form=new FormData();form.append("image",new Blob([bytes], {
      type:mime
    }),name||"photo.jpg");let r=await fetch(MEDIA, {
      method:"POST",body:form
    });let d=await r.json();if(!r.ok||d.success===false)throw Error(d.message||"No se pudo subir la imagen al servidor");return d.url
  }
  async function one(input,key,max,q) {
    let f=input.files?.[0];if(!f||!cur)return;try {
      stat("Procesando imagen…");cur[key]=await uploadImage(await readImage(f,max,q),f.name);let target= {
        cover:"coverPreview",logo:"logoPreview",backgroundImage:"backgroundPreview"
      }
      [key];$("#"+target).innerHTML='<img src="'+esc(cur[key])+'">';if(key==="logo")$("#removeLogo").classList.remove("hidden");preview();stat("Cambios pendientes");toast("Imagen cargada y optimizada")
    }
    catch(e) {
      toast(e.message,"error")
    }
    input.value=""
  }
  async function many(input) {
    let fs=[...(input.files||[])];if(!cur||!fs.length)return;if(cur.gallery.length+fs.length>18) {
      toast("La galería admite hasta 18 fotos.","error");input.value="";return
    }
    try {
      stat("Optimizando fotografías…");for(let f of fs)cur.gallery.push(await uploadImage(await readImage(f,1280,.7),f.name));galleryManager();preview();stat("Cambios pendientes");toast(fs.length+" foto(s) añadida(s)")
    }
    catch(e) {
      toast(e.message,"error")
    }
    input.value=""
  }
  async function save(st) {
    let c=read();if(!c.clientName) {
      step("details");$("#clientName").focus();toast("Escribe el nombre del cliente o empresa.","error");return false
    }
    if(c.kind!=="profile"&&!c.title) {
      step("details");$("#listingTitle").focus();toast("Escribe el título del anuncio.","error");return false
    }
    c.status=st||c.status||"draft";c.updatedAt=new Date().toISOString();c.createdAt=c.createdAt||c.updatedAt;stat("Guardando…");try {
      let r=await post( {
        action:"save",card:c
      });c.id=r.card?.id||r.id;if(!c.id)throw Error("No se recibió ID del servidor");cur=c;let i=cards.findIndex(x=>String(x.id)===String(c.id));i<0?cards.unshift(copy(c)):cards[i]=copy(c);try {
        try {
          localStorage.setItem(KEY,JSON.stringify(cards))
        }
        catch {
        }
      }
      catch {
      }
      stat("Guardado en servidor");$("#editorIdLabel").textContent=(c.status==="published"?"Publicado":"Borrador")+" · "+c.id;dashboard();show("dash");dashboard();toast(c.status==="published"?"Tarjeta publicada.":"Proyecto guardado en tu inventario para editar después.");return true
    }
    catch(e) {
      stat("No sincronizado",true);toast("No se guardó en el servidor: "+e.message,"error");return false
    }
  }
  function slug(v) {
    return String(v||"tarjeta-digital").normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase().replace(/[^a-z0-9]+/g,"-").replace(/^-+|-+$/g,"").slice(0,70)||"tarjeta-digital"
  }
  function cardUrl(c) {
    let u=new URL(location.href.split("?")[0]);u.searchParams.set("card",c.id);if(c.publicName)u.searchParams.set("nombre",slug(c.publicName));return u.href
  }
  let publicManifestUrl=null;
  function setPublicCardManifest(c) {
    const link=document.querySelector('link[rel="manifest"]');
    if(!link||!c?.id)return;
    const base=new URL("./",location.href).href;
    const manifest={
      name:(c.publicName||c.title||c.clientName||"Tarjeta digital")+" | LAV Digital",
      short_name:(c.publicName||c.title||c.clientName||"LAV Tarjeta").slice(0,24),
      description:"Tarjeta digital de "+(c.clientName||"LAV Digital"),
      start_url:location.href,
      scope:base,
      display:"standalone",
      orientation:"portrait",
      background_color:c.theme?.background||"#101820",
      theme_color:c.theme?.background||"#101820",
      icons:[{src:new URL("icon.svg",base).href,sizes:"any",type:"image/svg+xml",purpose:"any maskable"}]
    };
    try {
      const next=URL.createObjectURL(new Blob([JSON.stringify(manifest)],{type:"application/manifest+json"}));
      link.href=next;
      if(publicManifestUrl)URL.revokeObjectURL(publicManifestUrl);
      publicManifestUrl=next;
    } catch {}
  }
  function saveContact(c) {
    if(!c?.id) {
      toast("Primero publica la tarjeta para guardar el contacto.", "error");
      return;
    }
    const contactUrl=new URL("api/contact.php?id="+encodeURIComponent(c.id),location.href.split("?")[0]).href;
    // Abrir la vCard desde el gesto del usuario permite que el navegador móvil
    // la entregue al selector de Contactos/Importar cuando el sistema lo admite.
    const opened=window.open(contactUrl,"_blank","noopener");
    if(!opened) window.location.href=contactUrl;
  }
  async function share(c) {
    if(!c?.id)return toast("Guarda la tarjeta primero.","error");
    const u=cardUrl(c);
    if(navigator.share) {
      try { await navigator.share({title:c.publicName||c.title||c.clientName||"Tarjeta digital",text:"Te comparto esta tarjeta digital",url:u}); return; }
      catch(e) { if(e?.name==="AbortError")return; }
    }
    try {
      await navigator.clipboard.writeText(u);
      toast("Enlace copiado. Ya puedes pegarlo en WhatsApp o donde quieras compartirlo.");
    } catch {
      let d=document.createElement("div");d.className="lightbox";d.innerHTML='<div style="background:white;color:#222;padding:24px;border-radius:14px;max-width:90vw;overflow-wrap:anywhere"><h3>Enlace de la tarjeta</h3><p>'+esc(u)+'</p><button class="button button-primary" data-close>Copiar enlace</button></div>';document.body.append(d);d.querySelector("button").onclick=async()=>{try{await navigator.clipboard.writeText(u);d.remove();toast("Enlace copiado.");}catch{}} 
    }
  }
  function ask(title,msg) {
    return new Promise(resolve=> {
      let d=$("#confirmDialog");$("#confirmTitle").textContent=title;$("#confirmMessage").textContent=msg;d.addEventListener("close",()=>resolve(d.returnValue==="confirm"), {
        once:true
      });d.showModal()
    })
  }
  async function action(a,id) {
    let c=cards.find(x=>String(x.id)===String(id));if(!c)return;if(a==="edit")return edit(id);if(a==="more")return $("#more-"+CSS.escape(id)).classList.toggle("hidden");if(a==="link") {
      if(c.status!=="published")return toast("Publica el anuncio antes de compartirlo.","error");return share(c)
    }
    if(a==="duplicate") {
      cur= {
        ...copy(c),id:null,createdAt:null,updatedAt:null,status:"draft",title:(c.title||"Anuncio")+" — copia"
      };fill(cur);show("edit");step("details");renderTemplates();preview();toast("Copia independiente creada");return
    }
    if(a==="delete") {
      if(!await ask("Eliminar proyecto","Se eliminará este anuncio permanentemente."))return;try {
        await post( {
          action:"delete",id
        });cards=cards.filter(x=>String(x.id)!==String(id));try {
          localStorage.setItem(KEY,JSON.stringify(cards))
        }
        catch {
        };dashboard();toast("Proyecto eliminado")
      }
      catch(e) {
        toast(e.message,"error")
      }
      return
    }
    if(a==="archive"&&!await ask("Archivar proyecto","Se ocultará el anuncio del inventario activo."))return;if(a==="toggle"||a==="archive") {
      let d= {
        ...copy(c),theme: {
          ...c.theme
        }
      };d.status=a==="archive"?"archived":c.status==="published"?"draft":"published";d.updatedAt=new Date().toISOString();try {
        let r=await post( {
          action:"save",card:d
        });d.id=r.id||r.card?.id||d.id;let i=cards.findIndex(x=>x.id===d.id);cards[i]=d;try {
          localStorage.setItem(KEY,JSON.stringify(cards))
        }
        catch {
        };dashboard();toast(d.status==="published"?"Publicado":d.status==="archived"?"Archivado":"Despublicado")
      }
      catch(e) {
        toast(e.message,"error")
      }
    }
  }
  async function publicPage(id) {
    show("public");$(".topbar").classList.add("hidden");$("#publicMount").innerHTML='<div class="public-shell" style="color:white;padding:50px;text-align:center">Cargando tarjeta…</div>';let c;try {
      let r=await get( {
        id
      });c=r.card||r.data
    }
    catch(e) {
      try {
        c=JSON.parse(localStorage.getItem(KEY)||"[]").find(x=>String(x.id)===String(id))
      }
      catch {
      }
      if(!c)return noPublic("No se pudo recuperar el anuncio desde el servidor.")
    }
    if(c.status!=="published")return noPublic("El anuncio no está publicado o ya no está disponible.");setPublicCardManifest(c);document.body.classList.add("public-card-mode");let all=[];try {
      all=(await get( {
        action:"list"
      })).cards||[]
    }
    catch {
      try {
        all=JSON.parse(localStorage.getItem(KEY)||"[]")
      }
      catch {
      }
    }
    cards=all;let catalog=c.kind==="profile"?all.filter(x=>x.clientName===c.clientName&&x.status==="published"&&x.kind!=="profile"):[];$("#publicMount").innerHTML='<div class="public-shell">'+card(c, {
      publicMode:true,catalog
    })+'</div>';document.title=(c.publicName||c.title||c.clientName)+" | Tarjeta digital";document.querySelector('meta[name="theme-color"]')?.setAttribute("content",c.theme?.background||"#101116")
  }
  function noPublic(m) {
    $("#publicMount").innerHTML='<div class="public-shell" style="min-height:100vh;padding:15vh 24px;color:white;text-align:center"><h1>Esta tarjeta no está disponible</h1><p style="color:#b4b6c3;line-height:1.7">'+esc(m)+'</p><a href="'+esc(location.pathname)+'" class="button button-primary">Ir a LAV Digital</a></div>'
  }
  function qr(c) {
    let u=cardUrl(c),o=document.createElement("div");o.className="lightbox";o.innerHTML='<button class="lightbox-close">×</button><div style="background:white;color:#222;padding:24px;border-radius:16px;text-align:center"><img alt="Código QR" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=8&data='+encodeURIComponent(u)+'" style="width:230px;height:230px"><h3>Código QR de tu tarjeta</h3><button class="button button-primary" data-copy>Copiar enlace</button></div>';document.body.append(o);o.querySelector(".lightbox-close").onclick=()=>o.remove();o.querySelector("[data-copy]").onclick=()=>share(c)
  }
  function lightbox(c,index) {
    let a=[c.cover,...(c.gallery||[])].filter(Boolean),i=index<0?0:Number(index)+1;if(!a.length)return;let o=document.createElement("div");o.className="lightbox";function draw() {
      o.innerHTML='<button class="lightbox-close">×</button><button class="lightbox-prev">‹</button><img src="'+esc(a[i])+'" alt="Foto ampliada"><button class="lightbox-next">›</button>';o.querySelector(".lightbox-close").onclick=()=>o.remove();o.querySelector(".lightbox-prev").onclick=()=> {
        i=(i-1+a.length)%a.length;draw()
      };o.querySelector(".lightbox-next").onclick=()=> {
        i=(i+1)%a.length;draw()
      }
    }
    draw();document.body.append(o);o.onclick=e=> {
      if(e.target===o)o.remove()
    }
  }
  function init() {
    ["newListingTop","newListingHero","newListingInventory","newListingEmpty"].forEach(id=>$("#"+id).onclick=newCard);$("#scrollInventory").onclick=()=>$("#inventorySection").scrollIntoView( {
      behavior:"smooth"
    });$("#backToInventory").onclick=()=> {
      show("dash");dashboard()
    };["saveDraftBtn","saveDraftBottom"].forEach(id=>$("#"+id).onclick=()=>save("draft"));["publishBtn","publishBottom"].forEach(id=>$("#"+id).onclick=async()=> {
      if(!cur)return;let n=prompt("Elige el nombre con el que se compartirá esta tarjeta:",cur.publicName||cur.title||cur.clientName||"Mi tarjeta");if(n===null)return;n=n.trim();if(!n) {
        toast("Escribe un nombre para la tarjeta antes de publicar.","error");return
      }
      cur.publicName=n;if(await save("published")) {
        dashboard();share(cur)
      }
    });$$(".step-tab").forEach(b=>b.onclick=()=> {
      read();step(b.dataset.step);preview()
    });$("#listingType").onchange=()=> {
      let k=$("#listingType").value;$("#propertyFields").classList.toggle("hidden",k!=="property");$("#vehicleFields").classList.toggle("hidden",k!=="vehicle");$("#operationField").classList.toggle("hidden",k!=="property");preview()
    };["searchInput","typeFilter","statusFilter","clientFilter"].forEach(id=>$("#"+id).addEventListener(id==="searchInput"?"input":"change",dashboard));$("#templateSearch").oninput=()=> {
      templatePage=1;renderTemplates()
    };$("#templateCategory").onchange=()=> {
      templatePage=1;renderTemplates()
    };$("#templateSkin").onchange=()=> {
      templatePage=1;renderTemplates()
    };$("#loadMoreTemplates").onclick=()=> {
      templatePage++;renderTemplates()
    };$("#templateGrid").onclick=e=> {
      let b=e.target.closest("[data-template]");if(!b)return;read();let selected=T.find(x=>x.id===b.dataset.template);cur.templateId=b.dataset.template;if(selected)cur.theme= {
        ...cur.theme,...selected.colors
      };fill(cur);renderTemplates();preview();stat("Diseño cambiado; guarda para conservarlo")
    };$$("input,select,textarea",$("#editorView")).forEach(el=> {
      if(!["coverFile","galleryFiles","logoFile","backgroundFile"].includes(el.id))el.addEventListener("input",()=> {
        read();preview();stat("Cambios pendientes")
      })
    });$("#coverFile").onchange=()=>one($("#coverFile"),"cover",1800,.8);$("#logoFile").onchange=()=>one($("#logoFile"),"logo",500,.84);$("#removeLogo").onclick=()=> {
      if(!cur)return;cur.logo="";$("#logoPreview").innerHTML="";$("#removeLogo").classList.add("hidden");preview();stat("Logotipo quitado; guarda para conservar el cambio")
    };$("#backgroundFile").onchange=()=>one($("#backgroundFile"),"backgroundImage",1400,.72);$("#galleryFiles").onchange=()=>many($("#galleryFiles"));$("#removeBackground").onclick=()=> {
      cur.backgroundImage="";$("#backgroundPreview").innerHTML="";preview()
    };$("#resetDesign").onclick=()=> {
      cur.theme=copy(B.theme);fill(cur);preview()
    };$("#galleryManager").onclick=e=> {
      let b=e.target.closest("[data-remove-photo]");if(!b)return;cur.gallery.splice(+b.dataset.removePhoto,1);galleryManager();preview()
    };let drag=null;$("#galleryManager").ondragstart=e=> {
      let x=e.target.closest("[data-photo-index]");if(x)drag=+x.dataset.photoIndex
    };$("#galleryManager").ondragover=e=> {
      if(e.target.closest("[data-photo-index]"))e.preventDefault()
    };$("#galleryManager").ondrop=e=> {
      let x=e.target.closest("[data-photo-index]");if(!x||drag===null)return;e.preventDefault();let [p]=cur.gallery.splice(drag,1);cur.gallery.splice(+x.dataset.photoIndex,0,p);drag=null;galleryManager();preview()
    };$$(".device-switch button").forEach(b=>b.onclick=()=> {
      $$(".device-switch button").forEach(x=>x.classList.toggle("active",x===b));$("#previewCard").classList.toggle("desktop-preview",b.dataset.device==="desktop")
    });$("#inventoryGrid").onclick=e=> {
      let b=e.target.closest("[data-action]");if(b)action(b.dataset.action,b.dataset.id)
    };document.addEventListener("click",e=> {
      let el=e.target.closest(".digital-card");if(!el)return;let id=el.dataset.cardId,c=id==="preview"?cur:cards.find(x=>String(x.id)===String(id));if(e.target.closest("[data-gallery-prev]")||e.target.closest("[data-gallery-next]")) {
        if(!c?.gallery?.length)return;let d=e.target.closest("[data-gallery-next]")?1:-1;if(id==="preview") {
          gidx=(gidx+d+c.gallery.length)%c.gallery.length;preview()
        }
        else {
          let im=el.querySelector(".dc-gallery-viewer img"),i=c.gallery.indexOf(im.src);i=(i+d+c.gallery.length)%c.gallery.length;let v=el.querySelector(".dc-gallery-viewer");v.classList.add("changing");setTimeout(()=> {
            im.src=c.gallery[i];el.querySelector(".dc-gallery-count").textContent=(i+1)+" / "+c.gallery.length;v.classList.remove("changing")
          },160)
        }
        return
      }
      let go=e.target.closest("[data-gallery-go]");if(go&&c?.gallery?.length) {
        let i=+go.dataset.galleryGo;if(id==="preview") {
          gidx=i;preview()
        }
        else {
          let v=el.querySelector(".dc-gallery-viewer");v.classList.add("changing");setTimeout(()=> {
            el.querySelector(".dc-gallery-viewer img").src=c.gallery[i];el.querySelector(".dc-gallery-count").textContent=(i+1)+" / "+c.gallery.length;v.classList.remove("changing")
          },160)
        }
        return
      }
      let lb=e.target.closest("[data-lightbox-index]");if(lb&&c) {
        lightbox(c,+lb.dataset.lightboxIndex);return
      }
      if(e.target.closest("[data-save-contact]")) {
        saveContact(c);return
      }
      if(e.target.closest("[data-share-card]")) {
        share(c);return
      }
      if(e.target.closest("[data-show-qr]")&&c?.id)qr(c)
    })
  }
  async function boot() {
    init();let id=new URLSearchParams(location.search).get("card");if(id) {
      show("public");$(".topbar").classList.add("hidden");await publicPage(id);return
    }
    try {
      let d=await get( {
        action:"list"
      });cards=d.cards||[];try {
        localStorage.setItem(KEY,JSON.stringify(cards))
      }
      catch {
      }
      stat("Sincronizado")
    }
    catch(e) {
      cards=[];stat("No se pudo conectar al servidor",true)
    }
    dashboard();show("dash")
  }
  boot();;
})();