
const $ = sel => document.querySelector(sel);
const postsEl = $('#posts');
const toastEl = $('#toast');
const postBtn = $('#postBtn');

function showToast(msg){
  toastEl.textContent = msg;
  toastEl.classList.add('show');
  setTimeout(()=>toastEl.classList.remove('show'), 2200);
}

function escapeHTML(str){
  return str.replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[ch]));
}

function daysLeft(createdAt){
  const ttlMs = 31*24*60*60*1000;
  const left = createdAt*1000 + ttlMs - Date.now();
  return Math.max(0, Math.ceil(left/(24*60*60*1000)));
}

async function api(action, data){
  const opts = { method: 'GET' };
  if (action === 'add' || action === 'delete'){
    opts.method = 'POST';
    opts.headers = {'Content-Type':'application/json'};
    opts.body = JSON.stringify(data||{});
  }
  const res = await fetch(`api.php?api=${action}`, opts);
  if (!res.ok) throw new Error('Request failed');
  return await res.json();
}

function renderPosts(list){
  if (!list.length){
    postsEl.innerHTML = `<div class="empty">No active posts yet. Be the first to post!</div>`;
    return;
  }
  postsEl.innerHTML = '';
  for (const p of list){
    const el = document.createElement('article');
    el.className = 'post';
    el.dataset.id = p.id;
    el.innerHTML = `
      <div class="meta">
        <span class="pill">${escapeHTML(p.category||'General')}</span>
        <span class="tiny">Posted: ${escapeHTML(p.created_ymd || '')}</span>
        <span class="tiny right">Expires in ${daysLeft(p.created_at)} day(s)</span>
      </div>
      <div class="subject">${escapeHTML(p.subject)}</div>
      ${p.details ? `<div class="details">${escapeHTML(p.details)}</div>` : ''}
    `;

    let clicks = 0; let timer = null;
    const reset = () => { clicks = 0; if (timer) { clearTimeout(timer); timer=null; } };
    el.addEventListener('click', async () => {
      clicks++;
      if (clicks === 1){ timer = setTimeout(reset, 3000); }
      if (clicks >= 6){
        reset();
        const id = el.dataset.id;
        try{
          const resp = await api('delete', { id });
          if (resp.ok){
            el.remove();
            showToast('Post removed');
            if (!postsEl.children.length){ renderPosts([]); }
          } else {
            showToast('Could not remove (try refresh)');
          }
        } catch(e){ showToast('Network error while removing'); }
      }
    });

    postsEl.appendChild(el);
  }
}

async function refresh(){
  try{
    const data = await api('list');
    renderPosts(data.posts||[]);
    $('#lastRefreshed').textContent = `Last refreshed ${new Date().toLocaleString()}`;
  } catch(e){
    postsEl.innerHTML = `<div class="empty">Failed to load posts. Please refresh.</div>`;
  }
}

$('#postForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  postBtn.disabled = true;
  const subject = $('#subject').value.trim();
  const details = $('#details').value.trim();
  const category = $('#category').value;
  if (!subject){ showToast('Subject is required'); postBtn.disabled=false; return; }
  try{
    const resp = await api('add', { subject, details, category });
    if (resp.ok){
      $('#subject').value = '';
      $('#details').value = '';
      $('#category').value = 'General';
      showToast('Posted!');
      refresh();
    } else {
      showToast(resp.error || 'Could not post');
    }
  } catch(err){
    showToast('Network error while posting');
  } finally { postBtn.disabled = false; }
});

$('#clearBtn').addEventListener('click', ()=>{
  $('#postForm').reset();
});

refresh();
