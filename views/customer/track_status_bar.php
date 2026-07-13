<style>
    body{
        background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 102, 0, 0.9));
    }
.order-notify-icon {
    position: fixed;
    top: 6px;
    right: 10px; 
    z-index: 1500;
    display: none;
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: linear-gradient(90deg,#ff7b2f,#ff6a00);
    color: #fff;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 28px rgba(10,10,10,0.06);
    cursor: pointer;
    border: 2px solid rgba(255,255,255,0.9);
    font-size: 20px;
}
.order-notify-icon:hover { transform: translateY(-3px); transition:.12s; }

/* badge */
.order-notify-icon .badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    border-radius: 999px;
    background:#2ecc71;
    color:#fff;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:11px;
    padding:0 5px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
}

/* ---------------- Modal overlay & card ---------------- */
.order-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(8,8,10,0.42);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1550;
    padding: 18px;
}

.order-modal {
    width: min(920px, 96%);
    max-height: 86vh;
    overflow: auto;
    background: linear-gradient(180deg,#fff,#fffaf6);
    border-radius: 14px;
    box-shadow: 0 28px 60px rgba(8,10,12,0.28);
    border: 1px solid rgba(0,0,0,0.06);
    padding: 12px;
    animation: modalIn .14s ease;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
    display: flex;
    flex-direction: column;
    padding: 0; /* remove outer padding so sticky header touches top */
    overflow: hidden; /* prevent double scrollbars */
}
.order-modal::-webkit-scrollbar {
    display: none;
}
@keyframes modalIn { from { transform: translateY(8px) scale(.995); opacity:0 } to { transform: translateY(0) scale(1); opacity:1 } }

/* modal header */

.order-modal .modal-head h3 { margin:0; font-size:2rem; color:rgb(255,102,0);font-family: 'Poppins', sans-serif;}
.order-modal .modal-head { color:#666; font-size:0.92rem; margin-top:3px; }

/* close button */
.order-modal .close-btn { border:0; margin: 0; background:transparent; font-size:20px; cursor:pointer; color:#888; padding:6px; border-radius:8px; }
.order-modal .close-btn:hover { background: rgba(0,0,0,0.03); color:#444; }

/* filter buttons */
.filter-group { display:flex; gap:8px; align-items:center; margin-top:6px; }
.filter-btn { border:1px solid #ff6a00; background:#fff; color:#ff6a00; padding:6px 10px; border-radius:8px; font-weight:700; cursor:pointer; }
.filter-btn.active, .filter-btn:hover { background:linear-gradient(90deg,#ff7b2f,#ff6a00); color:#fff; border-color:transparent; }

/* order list */
.order-list { display:flex; flex-direction:column; gap:12px; padding-bottom:8px; }

/* single order card (modal) */
.order-item {
    display:flex;
    gap:12px;
    align-items:flex-start;
    background: #fff;
    border-radius:12px;
    padding:14px;
    border:1px solid rgba(0,0,0,0.04);
    box-shadow: 0 14px 30px rgba(10,10,10,0.04);
    position:relative;
}
.order-item .thumb {
    width:72px;
    height:72px;
    border-radius:10px;
    overflow:hidden;
    background:#f7f7f7;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.order-item .thumb img { width:100%; height:100%; object-fit:cover; display:block; }

/* main content */
.order-item .main {
    flex:1;
    min-width:0;
}
.order-item h4 { margin:0; font-size:1.05rem; color:#222; display:flex; align-items:center; gap:10px; }
.order-item h4 .status-pill { margin-left:auto; font-weight:700; font-size:.86rem; padding:6px 10px; border-radius:999px; color:#fff; background:linear-gradient(90deg,#ff7b2f,#ff6a00); }

/* meta lines */
.order-item .meta { margin-top:8px; color:#444; font-size:0.92rem; }
.order-item .meta strong { color:#222; }

/* progress area (large) */
.order-item .progress-area { margin-top:12px; }

/* horizontal progress bar */
.progress-track { height:8px; background:#f1f1f1; border-radius:999px; overflow:hidden; }
.progress-fill { height:100%; width:0%; background:linear-gradient(90deg,#ff7b2f,#ff6a00); transition: width .45s cubic-bezier(.2,.9,.2,1); }

/* numbered step circles row */
.steps-row { display:flex; justify-content:space-between; align-items:center; gap:6px; margin-top:12px; }
.step {
    display:flex;
    flex-direction:column;
    align-items:center;
    width:calc((100% - 10px)/6);
    min-width:0;
}
.step .dot {
    width:30px;
    height:30px;
    border-radius:50%;
    background:#f0f0f0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:#aaa;
    box-shadow: 0 6px 14px rgba(10,10,10,0.03);
    transition: all .22s ease;
}
.step.completed .dot { background:#28b463; color:#fff; transform:scale(1.06); }
.step.in-progress .dot { background: linear-gradient(90deg,#ff7b2f,#ff6a00); color:#fff; transform:scale(1.08); }
.step .label { margin-top:6px; font-size:0.88rem; color:#757575; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* right-side actions */
.order-item .actions {
    display:flex;
    flex-direction:column;
    gap:8px;
    margin-left:12px;
    align-items:flex-end;
    flex-shrink:0;
}
.btn {
    padding:8px 10px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    font-weight:700;
    color:#fff;
}
.btn.track { background:linear-gradient(90deg,#ff7b2f,#ff6a00); text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
.btn.dismiss { background:#e74c3c; }
.close-modal-btn:hover {
  transform: rotate(90deg);
}

.close-modal-btn::before,
.close-modal-btn::after {
  position: absolute;
  width: 20px;
  height: 2px;
  background: #ffffffff;
  border-radius: 1px;
}

.close-modal-btn::before {
  transform: rotate(45deg);
}

.close-modal-btn::after {
  transform: rotate(-45deg);
}
/* canceled style */
.order-item.canceled { border-color: rgba(231,76,60,0.12); }
.order-item.canceled .status-pill { background: linear-gradient(90deg,#ff5b6a,#e74c3c); }

/* Dropdown styles for menu options */
.menu-options-toggle {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  padding: 6px 10px;
  margin-top: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #495057;
  transition: all 0.2s ease;
}

.menu-options-toggle:hover {
  background: #e9ecef;
}

.menu-options-toggle .toggle-icon {
  transition: transform 0.3s ease;
  font-size: 0.8rem;
  color: #6c757d;
}

.menu-options-toggle.expanded .toggle-icon {
  transform: rotate(180deg);
}

.menu-options-list {
  display: none;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-top: none;
  border-radius: 0 0 6px 6px;
  padding: 8px 12px;
  margin-top: -1px;
  font-size: 0.82rem;
  color: #495057;
}

.menu-options-list ul {
  margin: 0;
  padding-left: 16px;
}

.menu-options-list li {
  margin-bottom: 2px;
  line-height: 1.3;
}

.option-line-modal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #6c757d;
  margin: 3px 0;
}

.option-text-modal {
  flex: 1;
}

.option-price-modal {
  font-weight: 600;
  color: #28a745;
  min-width: 80px;
  text-align: right;
}

.option-price-modal.free {
  color: #6c757d;
  font-style: italic;
}

/* responsive */
@media (max-width:720px) {
    .order-item { flex-direction:column; align-items:stretch; }
    .order-item .thumb { width:100%; height:160px; border-radius:10px; }
    .order-item .actions { flex-direction:row; justify-content:flex-end; margin-left:0; margin-top:10px; }
    .steps-row { gap:8px; }
}
/* --- FIXED sticky modal header --- */


.order-modal .modal-head {
  position: sticky;
  top: 0;
  background-color: #fff;
  border-bottom: 1px solid #ccc;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px; 
  margin: 0;          
}

/* make inner content scrollable instead */
.order-modal .order-list {
  flex: 1 1 auto;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 12px 14px 20px;
}

.chat-box {
  margin-top: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  font-family: 'Poppins', sans-serif;
}

.chat-header {
  padding: 8px 12px;
  background: #f7f7f7;
  cursor: pointer;
  font-weight: 600;
  color: #333;
  transition: background .2s;
}

.chat-header:hover { background: #ff6a00; }

.chat-body {
  max-height: 0;
  overflow: hidden;
  transition: max-height .3s ease;
}

.chat-body.open { max-height: 200px; }

.messages {
  padding: 10px;
  font-size: 0.9rem;
  height: 140px;
  overflow-y: auto;
}

.message {
  background: #f5f5f5;
  padding: 6px 10px;
  border-radius: 6px;
  margin-bottom: 6px;
  width: fit-content;
}

.input-area {
  display: flex;
  border-top: 1px solid #eee;
}

.input-area input {
  flex: 1;
  padding: 6px 8px;
  border: none;
  outline: none;
}

.input-area button {
  border: none;
  background: linear-gradient(90deg,#ff7b2f,#ff6a00);
  color: white;
  padding: 6px 12px;
  cursor: pointer;
}
</style>
<style>
/* cancel reason overlay */
.cancel-reason-overlay {
  position: fixed;
  inset: 0;
  background: rgba(8,8,10,0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1600;
  padding: 16px;
}
.cancel-reason-card {
  width: min(460px, 96%);
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 24px 48px rgba(0,0,0,0.18);
  border: 1px solid rgba(0,0,0,0.06);
  padding: 14px;
}
.cancel-reason-card h4 { margin: 0 0 8px 0; color:#e74c3c; }
.cancel-reason-card p { margin: 0 0 8px 0; color:#555; font-size: .95rem; }
.cancel-reason-card textarea, .cancel-reason-card select {
  width: 100%;
  resize: vertical;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px;
  font-size: .96rem;
}
.cancel-reason-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:10px; }
.btn.secondary { background:#95a5a6; color:#fff; }
</style>

<!-- notify icon -->
<div id="orderNotifyIcon" class="order-notify-icon" title="Your orders (click to open)" role="button" aria-haspopup="dialog">
    📦
    <div class="badge" id="orderNotifyBadge" style="display:none">0</div>
</div>

<!-- modal overlay -->
<div id="orderModalOverlay" class="order-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="order-modal" role="document" aria-labelledby="ordersHeading">
        <div class="modal-head">
            <div>
                <h3 id="ordersHeading">Your orders</h3>
                <div class="filter-group" role="group" aria-label="Filter orders">
                    <button type="button" id="filterAcceptedBtn" class="filter-btn">Accepted</button>
                    <button type="button" id="filterCanceledBtn" class="filter-btn">Cancelled</button>
                </div>
            </div>
            <div>
                
                <button
        class="close-btn close-modal-btn" 
        id="closeOrderModal"
        aria-label="Close"
        type="button"
        style="
               width:50px; height:50px; 
               background:url('../../assets/images/cancel.jpg') no-repeat center center; 
               background-size:contain; 
               border:none; cursor:pointer;">
    </button>
            </div>
        </div>

        <div class="order-list" id="orderListContainer">
            <!-- populated by JS -->
        </div>

        <div style="text-align:right; color:#666; margin-top:8px;">
            <small>Orders are updated automatically.</small>
        </div>
    </div>
</div>

<!-- cancel reason dialog -->
<div id="cancelReasonOverlay" class="cancel-reason-overlay" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="cancel-reason-card" role="document">
    <h4>Cancel Order</h4>
    <p>Please select a reason (optional):</p>
    <select id="cancelReasonSelect">
      <option value="">-- Select a reason --</option>
      <option value="Changed my mind">Changed my mind</option>
      <option value="Ordered by mistake">Ordered by mistake</option>
      <option value="Found a better option">Found a better option</option>
      <option value="Delivery time too long">Delivery time too long</option>
      <option value="Other">Other</option>
    </select>
    <div class="cancel-reason-actions">
      <button type="button" class="btn secondary" id="cancelReasonCloseBtn">Close</button>
      <button type="button" class="btn dismiss" id="cancelReasonSubmitBtn">Confirm Cancel</button>
    </div>
  </div>
  
</div>

<div id="trackOverlay" class="order-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">
  <div class="order-modal" role="document" style="width:min(980px,96%);height:min(86vh,820px);padding:0;overflow:hidden;">
    <div class="modal-head">
      <h3>Track delivery</h3>
      <button class="close-btn" id="closeTrackOverlay" type="button">✕</button>
    </div>
    <div style="flex:1 1 auto; overflow:hidden;">
      <iframe id="trackFrame" src="about:blank" style="border:0;width:100%;height:100%;display:block;"></iframe>
    </div>
  </div>
  
</div>

<style>
    body{
        background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 102, 0, 0.9));
    }
.order-notify-icon {
    position: fixed;
    top: 6px;
    right: 10px; 
    z-index: 1500;
    display: none;
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: linear-gradient(90deg,#ff7b2f,#ff6a00);
    color: #fff;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 28px rgba(10,10,10,0.06);
    cursor: pointer;
    border: 2px solid rgba(255,255,255,0.9);
    font-size: 20px;
}
.order-notify-icon:hover { transform: translateY(-3px); transition:.12s; }

/* badge */
.order-notify-icon .badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    border-radius: 999px;
    background:#2ecc71;
    color:#fff;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:11px;
    padding:0 5px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
}

/* ---------------- Modal overlay & card ---------------- */
.order-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(8,8,10,0.42);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1550;
    padding: 18px;
}

.order-modal {
    width: min(920px, 96%);
    max-height: 86vh;
    overflow: auto;
    background: linear-gradient(180deg,#fff,#fffaf6);
    border-radius: 14px;
    box-shadow: 0 28px 60px rgba(8,10,12,0.28);
    border: 1px solid rgba(0,0,0,0.06);
    padding: 12px;
    animation: modalIn .14s ease;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
    display: flex;
    flex-direction: column;
    padding: 0; /* remove outer padding so sticky header touches top */
    overflow: hidden; /* prevent double scrollbars */
}
.order-modal::-webkit-scrollbar {
    display: none;
}
@keyframes modalIn { from { transform: translateY(8px) scale(.995); opacity:0 } to { transform: translateY(0) scale(1); opacity:1 } }

/* modal header */

.order-modal .modal-head h3 { margin:0; font-size:2rem; color:rgb(255,102,0);font-family: 'Poppins', sans-serif;}
.order-modal .modal-head { color:#666; font-size:0.92rem; margin-top:3px; }

/* close button */
.order-modal .close-btn { border:0; margin: 0; background:transparent; font-size:20px; cursor:pointer; color:#888; padding:6px; border-radius:8px; }
.order-modal .close-btn:hover { background: rgba(0,0,0,0.03); color:#444; }

/* filter buttons */
.filter-group { display:flex; gap:8px; align-items:center; margin-top:6px; }
.filter-btn { border:1px solid #ff6a00; background:#fff; color:#ff6a00; padding:6px 10px; border-radius:8px; font-weight:700; cursor:pointer; }
.filter-btn.active, .filter-btn:hover { background:linear-gradient(90deg,#ff7b2f,#ff6a00); color:#fff; border-color:transparent; }

/* order list */
.order-list { display:flex; flex-direction:column; gap:12px; padding-bottom:8px; }

/* single order card (modal) */
.order-item {
    display:flex;
    gap:12px;
    align-items:flex-start;
    background: #fff;
    border-radius:12px;
    padding:14px;
    border:1px solid rgba(0,0,0,0.04);
    box-shadow: 0 14px 30px rgba(10,10,10,0.04);
    position:relative;
}
.order-item .thumb {
    width:72px;
    height:72px;
    border-radius:10px;
    overflow:hidden;
    background:#f7f7f7;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.order-item .thumb img { width:100%; height:100%; object-fit:cover; display:block; }

/* main content */
.order-item .main {
    flex:1;
    min-width:0;
}
.order-item h4 { margin:0; font-size:1.05rem; color:#222; display:flex; align-items:center; gap:10px; }
.order-item h4 .status-pill { margin-left:auto; font-weight:700; font-size:.86rem; padding:6px 10px; border-radius:999px; color:#fff; background:linear-gradient(90deg,#ff7b2f,#ff6a00); }

/* meta lines */
.order-item .meta { margin-top:8px; color:#444; font-size:0.92rem; }
.order-item .meta strong { color:#222; }

/* progress area (large) */
.order-item .progress-area { margin-top:12px; }

/* horizontal progress bar */
.progress-track { height:8px; background:#f1f1f1; border-radius:999px; overflow:hidden; }
.progress-fill { height:100%; width:0%; background:linear-gradient(90deg,#ff7b2f,#ff6a00); transition: width .45s cubic-bezier(.2,.9,.2,1); }

/* numbered step circles row */
.steps-row { display:flex; justify-content:space-between; align-items:center; gap:6px; margin-top:12px; }
.step {
    display:flex;
    flex-direction:column;
    align-items:center;
    width:calc((100% - 10px)/6);
    min-width:0;
}
.step .dot {
    width:30px;
    height:30px;
    border-radius:50%;
    background:#f0f0f0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:#aaa;
    box-shadow: 0 6px 14px rgba(10,10,10,0.03);
    transition: all .22s ease;
}
.step.completed .dot { background:#28b463; color:#fff; transform:scale(1.06); }
.step.in-progress .dot { background: linear-gradient(90deg,#ff7b2f,#ff6a00); color:#fff; transform:scale(1.08); }
.step .label { margin-top:6px; font-size:0.88rem; color:#757575; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* right-side actions */
.order-item .actions {
    display:flex;
    flex-direction:column;
    gap:8px;
    margin-left:12px;
    align-items:flex-end;
    flex-shrink:0;
}
.btn {
    padding:8px 10px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    font-weight:700;
    color:#fff;
}
.btn.track { background:linear-gradient(90deg,#ff7b2f,#ff6a00); text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
.btn.dismiss { background:#e74c3c; }
.close-modal-btn:hover {
  transform: rotate(90deg);
}

.close-modal-btn::before,
.close-modal-btn::after {
  position: absolute;
  width: 20px;
  height: 2px;
  background: #ffffffff;
  border-radius: 1px;
}

.close-modal-btn::before {
  transform: rotate(45deg);
}

.close-modal-btn::after {
  transform: rotate(-45deg);
}
/* canceled style */
.order-item.canceled { border-color: rgba(231,76,60,0.12); }
.order-item.canceled .status-pill { background: linear-gradient(90deg,#ff5b6a,#e74c3c); }

/* Dropdown styles for menu options */
.menu-options-toggle {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  padding: 6px 10px;
  margin-top: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #495057;
  transition: all 0.2s ease;
  user-select: none;
  -webkit-user-select: none;
}

.menu-options-toggle:hover {
  background: #e9ecef;
}

.menu-options-toggle .toggle-icon {
  transition: transform 0.3s ease;
  font-size: 0.8rem;
  color: #6c757d;
}

.menu-options-toggle.expanded .toggle-icon {
  transform: rotate(180deg);
}

.menu-options-list {
  display: none;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-top: none;
  border-radius: 0 0 6px 6px;
  padding: 8px 12px;
  margin-top: -1px;
  font-size: 0.82rem;
  color: #495057;
  pointer-events: auto;
  position: relative;
  z-index: 1;
}

.menu-options-list ul {
  margin: 0;
  padding-left: 16px;
}

.menu-options-list li {
  margin-bottom: 2px;
  line-height: 1.3;
}

.menu-options-list * {
  pointer-events: auto;
}

.option-line-modal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #6c757d;
  margin: 3px 0;
}

.option-text-modal {
  flex: 1;
}

.option-price-modal {
  font-weight: 600;
  color: #28a745;
  min-width: 80px;
  text-align: right;
}

.option-price-modal.free {
  color: #6c757d;
  font-style: italic;
}

/* responsive */
@media (max-width:720px) {
    .order-item { flex-direction:column; align-items:stretch; }
    .order-item .thumb { width:100%; height:160px; border-radius:10px; }
    .order-item .actions { flex-direction:row; justify-content:flex-end; margin-left:0; margin-top:10px; }
    .steps-row { gap:8px; }
}
/* --- FIXED sticky modal header --- */


.order-modal .modal-head {
  position: sticky;
  top: 0;
  background-color: #fff;
  border-bottom: 1px solid #ccc;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px; 
  margin: 0;          
}

/* make inner content scrollable instead */
.order-modal .order-list {
  flex: 1 1 auto;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 12px 14px 20px;
}


</style>
<style>
/* cancel reason overlay */
.cancel-reason-overlay {
  position: fixed;
  inset: 0;
  background: rgba(8,8,10,0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1600;
  padding: 16px;
}
.cancel-reason-card {
  width: min(460px, 96%);
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 24px 48px rgba(0,0,0,0.18);
  border: 1px solid rgba(0,0,0,0.06);
  padding: 14px;
}
.cancel-reason-card h4 { margin: 0 0 8px 0; color:#e74c3c; }
.cancel-reason-card p { margin: 0 0 8px 0; color:#555; font-size: .95rem; }
.cancel-reason-card textarea, .cancel-reason-card select {
  width: 100%;
  resize: vertical;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px;
  font-size: .96rem;
}
.cancel-reason-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:10px; }
.btn.secondary { background:#95a5a6; color:#fff; }
</style>

<!-- notify icon -->
<div id="orderNotifyIcon" class="order-notify-icon" title="Your orders (click to open)" role="button" aria-haspopup="dialog">
    📦
    <div class="badge" id="orderNotifyBadge" style="display:none">0</div>
</div>

<!-- modal overlay -->
<div id="orderModalOverlay" class="order-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="order-modal" role="document" aria-labelledby="ordersHeading">
        <div class="modal-head">
            <div>
                <h3 id="ordersHeading">Your active orders</h3>
                <div class="filter-group" role="group" aria-label="Filter orders">
                    <button type="button" id="filterAcceptedBtn" class="filter-btn">Accepted</button>
                    <button type="button" id="filterCanceledBtn" class="filter-btn">Cancelled</button>
                </div>
            </div>
            <div>
                
                <button
        class="close-btn close-modal-btn" 
        id="closeOrderModal"
        aria-label="Close"
        type="button"
        style="
               width:50px; height:50px; 
               background:url('../../assets/images/cancel.jpg') no-repeat center center; 
               background-size:contain; 
               border:none; cursor:pointer;">
    </button>
            </div>
        </div>

        <div class="order-list" id="orderListContainer">
            <!-- populated by JS -->
        </div>

        <div style="text-align:right; color:#666; margin-top:8px;">
            <small>Orders are updated automatically.</small>
        </div>
    </div>
</div>

<!-- cancel reason dialog -->
<div id="cancelReasonOverlay" class="cancel-reason-overlay" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="cancel-reason-card" role="document">
    <h4>Cancel Order</h4>
    <p>Please select a reason (optional):</p>
    <select id="cancelReasonSelect">
      <option value="">-- Select a reason --</option>
      <option value="Changed my mind">Changed my mind</option>
      <option value="Ordered by mistake">Ordered by mistake</option>
      <option value="Found a better option">Found a better option</option>
      <option value="Delivery time too long">Delivery time too long</option>
      <option value="Other">Other</option>
    </select>
    <div class="cancel-reason-actions">
      <button type="button" class="btn secondary" id="cancelReasonCloseBtn">Close</button>
      <button type="button" class="btn dismiss" id="cancelReasonSubmitBtn">Confirm Cancel</button>
    </div>
  </div>
  
</div>

<div id="trackOverlay" class="order-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">
  <div class="order-modal" role="document" style="width:min(980px,96%);height:min(86vh,820px);padding:0;overflow:hidden;">
    <div class="modal-head">
      <h3>Track delivery</h3>
      <button class="close-btn" id="closeTrackOverlay" type="button">✕</button>
    </div>
    <div style="flex:1 1 auto; overflow:hidden;">
      <iframe id="trackFrame" src="about:blank" style="border:0;width:100%;height:100%;display:block;"></iframe>
    </div>
  </div>
  
</div>

<script>

const TRACKING_INTERVAL = 5000;
let pollingTimer = null;

/* DOM refs */
const notifyIcon = document.getElementById('orderNotifyIcon');
const badgeEl = document.getElementById('orderNotifyBadge');
const modalOverlay = document.getElementById('orderModalOverlay');
const orderListContainer = document.getElementById('orderListContainer');
const closeModalBtn = document.getElementById('closeOrderModal');
const filterAcceptedBtn = document.getElementById('filterAcceptedBtn');
const filterCanceledBtn = document.getElementById('filterCanceledBtn');
let currentFilter = 'all';

function setFilter(val){
    if (currentFilter === val) {
        currentFilter = 'all';
    } else {
        currentFilter = val;
    }
    if (filterAcceptedBtn) filterAcceptedBtn.classList.toggle('active', currentFilter === 'accepted');
    if (filterCanceledBtn) filterCanceledBtn.classList.toggle('active', currentFilter === 'canceled');
    applyFilter();
}

function applyFilter(){
    const items = orderListContainer.querySelectorAll('.order-item');
    items.forEach(el => {
        const st = el.getAttribute('data-status') || '';
        if (currentFilter === 'all') {
            el.style.display = '';
        } else if (currentFilter === 'accepted') {
            const acceptedGroup = ['pending','accepted','preparing','ready','on_the_way','delivered'];
            el.style.display = acceptedGroup.includes(st) ? '' : 'none';
        } else {
            el.style.display = (st === currentFilter) ? '' : 'none';
        }
    });
}

if (filterAcceptedBtn) filterAcceptedBtn.addEventListener('click', () => setFilter('accepted'));
if (filterCanceledBtn) filterCanceledBtn.addEventListener('click', () => setFilter('canceled'));

/* helpers */
const escapeHtml = (unsafe) => {
    if (unsafe === undefined || unsafe === null) return '';
    return String(unsafe).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');
};

const formatCurrency = (amount, currency='MMK') => {
    if (typeof amount === 'number' && !isNaN(amount)) return amount.toLocaleString() + ' ' + currency;
    if (amount && (!isNaN(Number(amount)))) return Number(amount).toLocaleString() + ' ' + currency;
    return amount || '';
};

const STATUS_STEPS = ['pending','accepted','preparing','ready','on_the_way','delivered'];
const stepIndex = (status) => {
    const idx = STATUS_STEPS.indexOf(status);
    return idx === -1 ? 0 : idx;
};

/* ensure global functions exist (preserve integration) */
window.dismissOrder = window.dismissOrder || function(orderId) {
    try {
        let activeOrders = JSON.parse(localStorage.getItem('active_orders') || '[]');
        activeOrders = activeOrders.filter(o => o.order_id !== orderId);
        localStorage.setItem('active_orders', JSON.stringify(activeOrders));
        const el = document.getElementById(`order-${orderId}`);
        if (el) el.remove();
        refreshNotifyBadge();
        // if no orders left close modal automatically
        const remaining = JSON.parse(localStorage.getItem('active_orders') || '[]');
        if (!remaining || remaining.length === 0) {
            closeModal();
        }
    } catch (e) { console.error(e); }
};

window.toggleCardDetails = window.toggleCardDetails || function(orderId){
    const el = document.getElementById(`order-${orderId}`);
    if (!el) return;
    el.classList.toggle('expanded');
};

/* show/hide notify icon based on localStorage */
function refreshNotifyBadge() {
    try {
        const activeOrders = JSON.parse(localStorage.getItem('active_orders') || '[]') || [];
        const count = activeOrders.length;
        if (count > 0) {
            notifyIcon.style.display = 'inline-flex';
            badgeEl.style.display = 'inline-flex';
            badgeEl.textContent = count > 9 ? '9+' : String(count);
        } else {
            notifyIcon.style.display = 'none';
            badgeEl.style.display = 'none';
        }
    } catch (e) {
        notifyIcon.style.display = 'none';
        badgeEl.style.display = 'none';
    }
}

/* open/close modal */
function openModal(){
    refreshNotifyBadge();
    modalOverlay.style.display = 'flex';
    modalOverlay.setAttribute('aria-hidden', 'false');
    fetchActiveOrdersStatus();
    if (pollingTimer) clearInterval(pollingTimer);
    pollingTimer = setInterval(fetchActiveOrdersStatus, TRACKING_INTERVAL);
}
function closeModal(){
    modalOverlay.style.display = 'none';
    modalOverlay.setAttribute('aria-hidden', 'true');
    if (pollingTimer) { clearInterval(pollingTimer); pollingTimer = null; }
}

/* clicking the notify icon */
notifyIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = modalOverlay.style.display === 'flex';
    if (open) closeModal(); else openModal();
});
closeModalBtn.addEventListener('click', (e) => { e.stopPropagation(); closeModal(); });
modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) closeModal(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

function resolveThumbUrl(summary) {
    if (!summary) return '/foodandme/assets/images/default_restaurant.png';
    let logo = summary.logo || summary.restaurant_logo || summary.logo_url || summary.image || summary.thumb || null;
    if (!logo) return '/foodandme/assets/images/default_restaurant.png';
    logo = String(logo).trim();
    if (logo.match(/^https?:\/\//i)) return logo;
    if (logo.startsWith('/')) return logo;
    return '/foodandme/assets/images/' + logo;
}


/* render a single order card inside modal */
function renderOrderCard(orderId, data, summary) {
    const status = data.status || 'pending';
    const idx = stepIndex(status);
    const percent = Math.round((idx / (STATUS_STEPS.length - 1)) * 100);

    const thumbUrl = resolveThumbUrl(summary);
    const restaurant = (summary && summary.restaurant_name) ? summary.restaurant_name : 'Restaurant';
    const total = formatCurrency(summary && summary.total_amount, summary && summary.currency || 'MMK');

    let itemsHtml = '';
    
    if (summary && Array.isArray(summary.items) && summary.items.length) {
        itemsHtml = `<ul style="margin-top:8px;padding-left:16px;color:#444;">`;
        
        summary.items.forEach((item) => {
            // Check for menu options
            const menuOptions = item.options || [];
            const hasOptions = Array.isArray(menuOptions) && menuOptions.length > 0;
            
            if (hasOptions) {
                // Item with options - create dropdown
                itemsHtml += `
                    <li style="margin-bottom:12px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <strong>${escapeHtml(item.qty || '')} x ${escapeHtml(item.name || '')}</strong>
                                <div style="font-size: 12px; color: #666; margin-top: 2px;">
                                    ${formatCurrency(item.price || 0)} each
                                </div>
                            </div>
                            <div style="text-align: right; font-weight: bold; color: #e74c3c;">
                                ${formatCurrency((item.price || 0) * (item.qty || 1))}
                            </div>
                        </div>
                        <div class="menu-options-toggle" onclick="event.stopPropagation(); event.preventDefault(); toggleMenuOptions(event, this)">
                            <span>View Options (${menuOptions.length})</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="menu-options-list" onclick="event.stopPropagation()">
                            ${menuOptions.map(option => {
                                const optionName = option.option_name || 'Option';
                                const valueName = option.value_name || '';
                                const priceModifier = option.price_modifier || 0;
                                
                                return `
                                    <div class="option-line-modal">
                                        <span class="option-text-modal">
                                            ${escapeHtml(optionName)}${valueName ? ': ' + escapeHtml(valueName) : ''}
                                        </span>
                                        <span class="option-price-modal ${priceModifier > 0 ? '' : 'free'}">
                                            ${priceModifier > 0 ? `+${formatCurrency(priceModifier)}` : '(included)'}
                                        </span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </li>
                `;
            } else {
                // Regular item without options
                itemsHtml += `
                    <li style="margin-bottom:8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${escapeHtml(item.qty || '')} x ${escapeHtml(item.name || '')}</strong>
                                <div style="font-size: 12px; color: #666;">
                                    ${formatCurrency(item.price || 0)} each
                                </div>
                            </div>
                            <div style="font-weight: bold; color: #e74c3c;">
                                ${formatCurrency((item.price || 0) * (item.qty || 1))}
                            </div>
                        </div>
                    </li>
                `;
            }
        });
        
        itemsHtml += `</ul>`;
    } else {
        itemsHtml = `<div style="color:#777;margin-top:8px;font-size:.92rem">No items information available</div>`;
    }

    // steps labels (same as your image)
    const labels = ['Pending','Accepted','Preparing','Ready','On the way','Delivered'];

    // build steps HTML
    let stepsHtml = `<div class="steps-row">`;
    for (let i=0;i<labels.length;i++){
        let state = 'upcoming';
        if (i < idx) state = 'completed';
        if (i === idx) state = (status === 'canceled' ? 'upcoming' : 'in-progress');
        const dotContent = (i===4) ? '🚚' : String(i+1);
        stepsHtml += `<div class="step ${state}">
            <div class="dot">${dotContent}</div>
            <div class="label">${labels[i]}</div>
        </div>`;
    }
    stepsHtml += `</div>`;

    // create or replace DOM
    const existing = document.getElementById(`order-${orderId}`);
    const finalClass = (status === 'canceled') ? 'canceled' : '';
    const html = `
        <div class="order-item ${finalClass}" data-status="${escapeHtml(status)}" id="order-${orderId}">
            <div class="thumb" aria-hidden="true">
                <img src="${escapeHtml(thumbUrl)}" alt="${escapeHtml(restaurant)}">
            </div>
            <div class="main">
                <h4>Order #${escapeHtml(orderId)} </h4>
                <div class="meta"><strong>Restaurant:</strong> ${escapeHtml(restaurant)}</div>
                <div class="meta"><strong>Total:</strong> ${escapeHtml(total)}</div>
                ${ data && data.rider_name ? `<div class=\"meta\"><strong>Rider:</strong> ${escapeHtml(data.rider_name)}</div>` : '' }

                ${ status !== 'canceled' ? `
                <div class="progress-area">
                    <div class="progress-track" aria-hidden="true"><div class="progress-fill" style="width:${percent}%"></div></div>
                    ${stepsHtml}
                </div>` : '' }

                <div style="margin-top:12px;">
                    <strong>Order Items:</strong>
                    ${itemsHtml}
                </div>
                ${status === 'canceled' && data.cancellation_reason ? `<div style="margin-top:8px;color:#e74c3c"><strong>Reason:</strong> ${escapeHtml(data.cancellation_reason)}</div>` : ''}
                ${status === 'canceled' ? `<div class="meta"><strong>Date:</strong> ${new Date().toLocaleDateString()}</div>` : ''}
                
                <!-- Chat Box -->
                <div class="chat-box" id="chat-${orderId}" style="display: ${status === 'on_the_way' ? 'block' : 'none'};">
                    <div class="chat-header" onclick="toggleChat('${orderId}')">
                        💬 Chat with Rider
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="chat-body" id="chatBody-${orderId}">
                        <div class="messages" id="messages-${orderId}"></div>
                        <div class="input-area">
                            <input type="text" id="chatInput-${orderId}" placeholder="Type a message..." onkeypress="if(event.key === 'Enter') sendMessage('${orderId}')">
                            <button onclick="sendMessage('${orderId}')">Send</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions" aria-hidden="false">
                ${ (status === 'on_the_way' && (data && data.tracking_started)) ? `<button class="btn track" onclick="event.stopPropagation(); openTrack(${orderId});">Track</button>` : '' }
                ${ status === 'pending' ? `<button class="btn dismiss" onclick="event.stopPropagation(); openCancelReason(${orderId});">Cancel Order</button>` : '' }
                ${ status === 'canceled' ? `<button class="btn dismiss" onclick="event.stopPropagation(); dismissOrder(${orderId});">Delete</button>` : '' }
            </div>
        </div>
    `;

    // if (existing) {
    //     existing.outerHTML = html;
    // } else {
    //     // prepend so most-recent appears first
    //     orderListContainer.insertAdjacentHTML('afterbegin', html);
    // }
    // applyFilter();
    if (existing) {
         // SAVE current input (if exists)
        let oldInputValue = "";
        const oldInput = document.getElementById(`chatInput-${orderId}`);
        if (oldInput) oldInputValue = oldInput.value;

        existing.outerHTML = html;
        // RESTORE input text after re-render
        const newInput = document.getElementById(`chatInput-${orderId}`);
        if (newInput) newInput.value = oldInputValue;

       
    } else {
        // prepend so most-recent appears first
        orderListContainer.insertAdjacentHTML('afterbegin', html);
    }

    // only show chat if status is on the way
    const chatBox = document.getElementById(`chat-${orderId}`);
    if (chatBox) {
        if (status === 'on_the_way') {
            chatBox.style.display = 'block';
            // Auto-open chat when status changes to on_the_way
            const chatBody = document.getElementById(`chatBody-${orderId}`);
            if (chatBody) {
                chatBody.classList.add('open');
                localStorage.setItem(`chat_open_${orderId}`, '1');
            }
        } else {
            chatBox.style.display = 'none';
        }
    }

    loadChat(orderId);
    if (status === 'delivered') clearChat(orderId);

    // Restore previous open/closed state
    const savedState = localStorage.getItem(`chat_open_${orderId}`);
    if (savedState === '1') {
        const chatBody = document.getElementById(`chatBody-${orderId}`);
        if (chatBody) chatBody.classList.add('open');
}


    applyFilter();

}

/* fetch statuses from server for all orders in localStorage and render them */
async function fetchActiveOrdersStatus(){
    try {
        const activeOrders = JSON.parse(localStorage.getItem('active_orders') || '[]') || [];
        refreshNotifyBadge();

        // if none, show empty message
        if (!activeOrders || activeOrders.length === 0) {
            orderListContainer.innerHTML = '<div style="color:#666;padding:14px">No active orders</div>';
            // hide notify icon if empty
            refreshNotifyBadge();
            return;
        }

        // fetch each order's status
        const promises = activeOrders.map(tracked => {
            const orderId = tracked.order_id;
            return fetch(`get_order_status.php?order_id=${encodeURIComponent(orderId)}`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        // Use the summary from API response which includes items with options
                        renderOrderCard(orderId, data, data.summary || {});
                        // return null for final states (we'll remove them from active list)
                        return (data.status !== 'delivered' ) ? tracked : null;
                    } else {
                        // server says not found -> dismiss immediately
                        dismissOrder(orderId);
                        return null;
                    }
                }).catch(err => {
                    console.error('Order status fetch error for', orderId, err);
                    // render fallback with pending status
                    renderOrderCard(orderId, { status: 'pending' }, tracked.summary || {});
                    return tracked; // keep
                });
        });

        const results = await Promise.all(promises);
        const toKeep = results.filter(Boolean);
        localStorage.setItem('active_orders', JSON.stringify(toKeep));
        refreshNotifyBadge();

        // remove any DOM not in toKeep (but leave delivered/canceled wait for dismiss)
        const keepIds = (toKeep || []).map(o => Number(o.order_id));
        Array.from(orderListContainer.querySelectorAll('.order-item')).forEach(el => {
            const id = Number(el.id.replace('order-',''));
            if (!keepIds.includes(id)) {
                // if element not final already remove if not final
                if ( !el.classList.contains('delivered')) {
                    el.remove();
                }
            }
        });

        // if modal open and everything cleared -> close modal automatically
        if ((toKeep.length === 0) && modalOverlay.style.display === 'flex') {
            // but if there are final cards left (canceled/delivered) we let user dismiss them manually
            const remainingElems = orderListContainer.querySelectorAll('.order-item');
            if (remainingElems.length === 0) {
                closeModal();
            }
        }

    } catch (e) {
        console.error('fetchActiveOrdersStatus error', e);
    }
}

/* small background poll: keep badge up-to-date even if modal closed */
setInterval(() => {
    try {
        const activeOrders = JSON.parse(localStorage.getItem('active_orders') || '[]') || [];
        refreshNotifyBadge();
        if (activeOrders.length === 0) return;
        // when modal closed: perform light check to remove finished orders
        if (modalOverlay.style.display !== 'flex') {
            activeOrders.forEach(tracked => {
                fetch(`get_order_status.php?order_id=${encodeURIComponent(tracked.order_id)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success) {
                            if (data.status === 'delivered' ) {
                                // remove from local storage
                                let arr = JSON.parse(localStorage.getItem('active_orders') || '[]');
                                arr = arr.filter(a => a.order_id !== tracked.order_id);
                                localStorage.setItem('active_orders', JSON.stringify(arr));
                                refreshNotifyBadge();
                            }
                        } else {
                            // api returned not success -> dismiss local
                            let arr = JSON.parse(localStorage.getItem('active_orders') || '[]');
                            arr = arr.filter(a => a.order_id !== tracked.order_id);
                            localStorage.setItem('active_orders', JSON.stringify(arr));
                            refreshNotifyBadge();
                        }
                    }).catch(()=>{ /* silent */ });
            });
        }
    } catch(e){}
}, TRACKING_INTERVAL);

/* initial UI sync */
refreshNotifyBadge();

/* When modal open, poll for status regularly */
function openModalAndPoll() {
    openModal();
}


/* storage event (sync between tabs) */
window.addEventListener('storage', (e) => {
    if (e.key === 'active_orders') {
        refreshNotifyBadge();
        if (modalOverlay.style.display === 'flex') {
            // re-render
            fetchActiveOrdersStatus();
        }
    }
});

window.notifyOrderPlaced = function(open = false) {
    refreshNotifyBadge();
    if (open) openModal();
};

// Customer-triggered cancellation (with reason dialog)
let pendingCancelOrderId = null;
const cancelReasonOverlay = document.getElementById('cancelReasonOverlay');
const cancelReasonSelect = document.getElementById('cancelReasonSelect');
const cancelReasonSubmitBtn = document.getElementById('cancelReasonSubmitBtn');
const cancelReasonCloseBtn = document.getElementById('cancelReasonCloseBtn');

window.openCancelReason = window.openCancelReason || function(orderId){
    pendingCancelOrderId = orderId;
    if (cancelReasonSelect) cancelReasonSelect.value = '';
    if (cancelReasonOverlay) {
        cancelReasonOverlay.style.display = 'flex';
        cancelReasonOverlay.setAttribute('aria-hidden', 'false');
    }
};

window.closeCancelReason = window.closeCancelReason || function(){
    if (cancelReasonOverlay) {
        cancelReasonOverlay.style.display = 'none';
        cancelReasonOverlay.setAttribute('aria-hidden', 'true');
    }
    pendingCancelOrderId = null;
};

window.submitCancelReason = window.submitCancelReason || async function(){
    if (!pendingCancelOrderId) { closeCancelReason(); return; }
    const reason = cancelReasonSelect ? (cancelReasonSelect.value || '') : '';
    await cancelOrder(pendingCancelOrderId, reason);
    closeCancelReason();
};

// wire dialog buttons and backdrop
if (cancelReasonSubmitBtn) cancelReasonSubmitBtn.addEventListener('click', (e)=>{ e.stopPropagation(); submitCancelReason(); });
if (cancelReasonCloseBtn) cancelReasonCloseBtn.addEventListener('click', (e)=>{ e.stopPropagation(); closeCancelReason(); });
if (cancelReasonOverlay) cancelReasonOverlay.addEventListener('click', (e)=>{ if (e.target === cancelReasonOverlay) closeCancelReason(); });
document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape' && cancelReasonOverlay && cancelReasonOverlay.style.display === 'flex') closeCancelReason(); });

// Core cancel order request
window.cancelOrder = window.cancelOrder || async function(orderId, reason = ''){
    try {
        const form = new FormData();
        form.append('order_id', String(orderId));
        form.append('reason', reason || '');

        const resp = await fetch('cancel_order.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        });
        const data = await resp.json().catch(()=>({ success:false, message:'Invalid server response' }));
        if (data && data.success) {
            await fetchActiveOrdersStatus();
        } else {
            alert(data && data.message ? data.message : 'Failed to cancel order');
        }
    } catch (e) {
        console.error('cancelOrder error', e);
        alert('An error occurred while cancelling the order.');
    }
};

/* initial description if modal open / closed */
function openModal() {
    modalOverlay.style.display = 'flex';
    modalOverlay.setAttribute('aria-hidden', 'false');
    fetchActiveOrdersStatus();
    if (pollingTimer) clearInterval(pollingTimer);
    pollingTimer = setInterval(fetchActiveOrdersStatus, TRACKING_INTERVAL);
}
function closeModal() {
    modalOverlay.style.display = 'none';
    modalOverlay.setAttribute('aria-hidden', 'true');
    if (pollingTimer) { clearInterval(pollingTimer); pollingTimer = null; }
}

/* initial content */

const trackOverlay = document.getElementById('trackOverlay');
const trackFrame = document.getElementById('trackFrame');
const closeTrackOverlayBtn = document.getElementById('closeTrackOverlay');

window.openTrack = window.openTrack || function(orderId){
  if (!trackOverlay || !trackFrame) return;
  trackFrame.src = `track_order.php?order_id=${encodeURIComponent(orderId)}`;
  trackOverlay.style.display = 'flex';
  trackOverlay.setAttribute('aria-hidden','false');
};

window.closeTrack = window.closeTrack || function(){
  if (!trackOverlay || !trackFrame) return;
  trackOverlay.style.display = 'none';
  trackOverlay.setAttribute('aria-hidden','true');
  trackFrame.src = 'about:blank';
};

if (closeTrackOverlayBtn) closeTrackOverlayBtn.addEventListener('click', (e)=>{ e.stopPropagation(); closeTrack(); });
if (trackOverlay) trackOverlay.addEventListener('click', (e)=>{ if (e.target === trackOverlay) closeTrack(); });

// Add this function to handle dropdown toggles for menu options
window.toggleMenuOptions = function(event, element) {
    // Stop event from bubbling up to parent elements
    if (event) {
        event.stopPropagation();
        event.preventDefault();
        event.stopImmediatePropagation();
    }
    
    const isExpanded = element.classList.contains('expanded');
    const optionsList = element.nextElementSibling;
    
    // First close all other dropdowns
    const allDropdowns = document.querySelectorAll('.menu-options-toggle.expanded');
    allDropdowns.forEach(dropdown => {
        if (dropdown !== element) {
            dropdown.classList.remove('expanded');
            const otherList = dropdown.nextElementSibling;
            if (otherList) {
                otherList.style.display = 'none';
            }
        }
    });
    
    // Toggle current dropdown
    if (isExpanded) {
        element.classList.remove('expanded');
        if (optionsList) {
            optionsList.style.display = 'none';
        }
    } else {
        element.classList.add('expanded');
        if (optionsList) {
            optionsList.style.display = 'block';
        }
    }
};

// Add document click handler to close dropdowns when clicking elsewhere
document.addEventListener('click', function(event) {
    // Check if click is on a dropdown toggle or inside dropdown
    const clickedOnDropdown = event.target.closest('.menu-options-toggle') || 
                               event.target.closest('.menu-options-list');
    
    if (!clickedOnDropdown) {
        // Close all dropdowns
        const allDropdowns = document.querySelectorAll('.menu-options-toggle.expanded');
        allDropdowns.forEach(dropdown => {
            dropdown.classList.remove('expanded');
            const optionsList = dropdown.nextElementSibling;
            if (optionsList) {
                optionsList.style.display = 'none';
            }
        });
    }
});

orderListContainer.innerHTML = '<div style="color:#666;padding:12px"></div>';
let customerChatSocket = null;
let customerChatQueue = [];

function initCustomerChatSocket() {
  try {
    const scheme = location.protocol === 'https:' ? 'wss://' : 'ws://';
    const url = scheme + location.hostname + ':8080';
    customerChatSocket = new WebSocket(url);

    customerChatSocket.onopen = function() {
      if (customerChatQueue.length) {
        customerChatQueue.forEach(function(msg) {
          customerChatSocket.send(JSON.stringify(msg));
        });
        customerChatQueue = [];
      }
    };

    customerChatSocket.onmessage = function(event) {
      let data;
      try {
        data = JSON.parse(event.data);
      } catch (e) {
        return;
      }
      if (!data || data.type !== 'chat' || !data.order_id) {
        return;
      }

      const msgContainer = document.getElementById(`messages-${data.order_id}`);
      if (!msgContainer) {
        return;
      }

      const div = document.createElement('div');
      div.className = 'message';
      div.textContent = (data.from_role === 'rider' ? 'Rider: ' : '') + (data.text || '');
      msgContainer.appendChild(div);
      msgContainer.scrollTop = msgContainer.scrollHeight;

      const key = `chat_${data.order_id}`;
      let existing = [];
      try {
        existing = JSON.parse(localStorage.getItem(key) || '[]');
      } catch (e) {
        existing = [];
      }
      existing.push(`Rider: ${data.text || ''}`);
      localStorage.setItem(key, JSON.stringify(existing));
    };

    customerChatSocket.onclose = function() {
      setTimeout(function() {
        initCustomerChatSocket();
      }, 3000);
    };
    customerChatSocket.onerror = function() {};
  } catch (e) {}
}

function sendCustomerChatOverSocket(orderId, text) {
  const payload = {
    type: 'chat',
    order_id: orderId,
    from_role: 'customer',
    text: text
  };
  if (customerChatSocket && customerChatSocket.readyState === WebSocket.OPEN) {
    customerChatSocket.send(JSON.stringify(payload));
  } else {
    customerChatQueue.push(payload);
    if (!customerChatSocket || customerChatSocket.readyState === WebSocket.CLOSED) {
      initCustomerChatSocket();
    }
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCustomerChatSocket);
} else {
  initCustomerChatSocket();
}

function toggleChat(orderId) {
  const chatBody = document.getElementById(`chatBody-${orderId}`);
  if (!chatBody) return;
  chatBody.classList.toggle('open');
  localStorage.setItem(`chat_open_${orderId}`, chatBody.classList.contains('open') ? '1' : '0');
}

function handleKey(e, orderId) {
  if (e.key === 'Enter') {
    e.preventDefault();
    sendMessage(orderId);
  }
}

function sendMessage(orderId) {
  const input = document.getElementById(`chatInput-${orderId}`);
  if (!input) return;

  const text = input.value.trim();
  if (!text) return;

  const msgContainer = document.getElementById(`messages-${orderId}`);
  if (!msgContainer) return;

  const div = document.createElement('div');
  div.className = 'message';
  div.textContent = text;
  msgContainer.appendChild(div);
  msgContainer.scrollTop = msgContainer.scrollHeight;

  const key = `chat_${orderId}`;
  const existing = JSON.parse(localStorage.getItem(key) || '[]');
  existing.push(text);
  localStorage.setItem(key, JSON.stringify(existing));

  input.value = '';

  sendCustomerChatOverSocket(orderId, text);
}

function loadChat(orderId) {
  const key = `chat_${orderId}`;
  const msgContainer = document.getElementById(`messages-${orderId}`);
  if (!msgContainer) return;
  const saved = JSON.parse(localStorage.getItem(key) || '[]');
  saved.forEach(text => {
    const div = document.createElement('div');
    div.className = 'message';
    div.textContent = text;
    msgContainer.appendChild(div);
  });
}

function clearChat(orderId) {
  localStorage.removeItem(`chat_${orderId}`);
  const chatBox = document.getElementById(`chat-${orderId}`);
  if (chatBox) chatBox.remove();
}

</script>