<?php
$pageTitle = 'Book Your Stay';
require_once __DIR__ . '/../../helpers/Security.php';
$csrfToken = Security::generateCsrfToken();

$extraHead = <<<HTML
<style>
.booking-wrap { padding: 7rem 0 5rem; min-height: 100vh; background: var(--cream); }
.step-header { padding: 2rem 0 1rem; }
.step-indicator { display:flex; gap:0; margin-bottom:2.5rem; }
.step-item { flex:1; text-align:center; position:relative; }
.step-item::after { content:''; position:absolute; top:18px; right:-50%; width:100%; height:2px; background:#ddd; z-index:0; }
.step-item:last-child::after { display:none; }
.step-circle { width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;
  font-size:0.8rem;font-weight:700;border:2px solid #ddd;background:#fff;position:relative;z-index:1; }
.step-item.active .step-circle  { background:var(--gold);border-color:var(--gold);color:#fff; }
.step-item.done .step-circle    { background:var(--dark);border-color:var(--dark);color:#fff; }
.step-label { font-size:0.7rem;margin-top:0.3rem;color:var(--muted);letter-spacing:0.08em;text-transform:uppercase; }
.step-item.active .step-label   { color:var(--gold);font-weight:600; }
.booking-card { background:#fff;border:none;border-radius:4px;box-shadow:0 4px 30px rgba(0,0,0,0.06);padding:2.5rem; }
.price-preview { background:var(--dark);color:#fff;padding:1.5rem;border-radius:4px; }
.payment-option { border:2px solid #e5e5e5;padding:1.2rem 1.5rem;cursor:pointer;border-radius:4px;transition:all 0.2s; }
.payment-option.selected,.payment-option:hover { border-color:var(--gold); }
.payment-option input[type=radio] { display:none; }
.step-panel { display:none; }
.step-panel.active { display:block; }
</style>
HTML;
?>
<?php include '_header.php'; ?>

<div class="booking-wrap">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">

        <div class="step-header text-center mb-4">
          <div class="section-label mb-1">Reservation</div>
          <h2 style="font-size:2.4rem;">Book Your Stay</h2>
        </div>

        <!-- STEP INDICATORS -->
        <div class="step-indicator px-4 mb-4" id="stepIndicators">
          <?php $steps = ['Dates','Unit','Guest Info','Payment','Confirm']; ?>
          <?php foreach ($steps as $i => $s): ?>
          <div class="step-item <?= $i === 0 ? 'active' : '' ?>" id="stepItem<?= $i+1 ?>">
            <div class="step-circle"><?= $i+1 ?></div>
            <div class="step-label"><?= $s ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="booking-card">

              <!-- STEP 1: SELECT DATES & UNIT -->
              <div class="step-panel active" id="panel1">
                <h5 class="mb-4" style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;">Select Dates & Unit</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Check-in Date</label>
                    <input type="date" class="form-control" id="checkIn" min="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Check-out Date</label>
                    <input type="date" class="form-control" id="checkOut">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Unit Type</label>
                    <select class="form-select" id="unitTypeSelect">
                      <option value="">Select a unit type...</option>
                      <?php foreach ($units as $u): ?>
                      <option value="<?= $u['unit_type_id'] ?>"
                              data-price="<?= $u['price_per_night'] ?>"
                              data-name="<?= htmlspecialchars($u['name']) ?>"
                              data-max="<?= $u['max_guests'] ?>">
                        <?= htmlspecialchars($u['name']) ?> — ₱<?= number_format($u['price_per_night']) ?>/night (Max <?= $u['max_guests'] ?> guests)
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div id="availResult" class="mt-3"></div>
                <button class="btn btn-gold w-100 mt-4" onclick="checkAvailability()">Check Availability</button>
              </div>

             <!-- STEP 2: GUEST DETAILS -->
                  <div class="step-panel" id="panel2">
                    <h5 class="mb-4" style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;">Guest Information</h5>
                    <div class="row g-3">

                      <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Full Name *</label>
                        <input 
                          type="text" 
                          class="form-control" 
                          id="fullName" 
                          placeholder="Juan dela Cruz" 
                          maxlength="100"
                          pattern="^[A-Za-z\s\.']+$"
                          title="Name should only contain letters and spaces"
                          required
                        >
                      </div>

                      <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Email Address *</label>
                        <input 
                          type="email" 
                          class="form-control" 
                          id="emailAddr" 
                          placeholder="juan@email.com"
                          required
                        >
                      </div>

                      <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Phone Number *</label>
                        <input 
                          type="tel" 
                          class="form-control" 
                          id="phoneNum" 
                          placeholder="09123456789"
                          pattern="^09\d{9}$"
                          maxlength="11"
                          title="Enter a valid Philippine phone number (09XXXXXXXXX)"
                          oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                          required
                        >
                      </div>

                      <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">Number of Guests *</label>
                        <input 
                          type="number" 
                          class="form-control" 
                          id="numGuests" 
                          value="1" 
                          min="1"
                          max="20"
                          required
                        >
                      </div>

                      <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;letter-spacing:0.08em;text-transform:uppercase;">
                          Special Requests <span style="font-weight:400;text-transform:none;">(optional)</span>
                        </label>
                        <textarea 
                          class="form-control" 
                          id="specialReq" 
                          rows="3" 
                          placeholder="Late check-in, extra pillows, etc."
                        ></textarea>
                      </div>

                    </div>

                    <div class="d-flex gap-3 mt-4">
                      <button class="btn btn-outline-gold" onclick="goToStep(1)">
                        <i class="bi bi-arrow-left me-1"></i>Back
                      </button>
                      <button class="btn btn-gold flex-fill" onclick="validateGuestStep()">
                        Continue to Payment
                      </button>
                    </div>
                  </div>

              <!-- STEP 3: PAYMENT -->
              <div class="step-panel" id="panel3">
                <h5 class="mb-4" style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;">Payment Method</h5>
                <div class="d-flex flex-column gap-3">

                  <label class="payment-option" id="opt-gcash" onclick="selectPayment('gcash')">
                    <input type="radio" name="payment" value="gcash">
                    <div class="d-flex align-items-center gap-3">
                      <div style="width:48px;height:48px;background:#0066CC;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.75rem;">GCash</div>
                      <div>
                        <div class="fw-semibold">GCash</div>
                        <div class="text-muted" style="font-size:0.82rem;">Pay via GCash e-wallet. Instant confirmation.</div>
                      </div>
                      <i class="bi bi-check-circle-fill ms-auto text-success" id="chk-gcash" style="display:none"></i>
                    </div>
                  </label>

                  <label class="payment-option" id="opt-online_payment" onclick="selectPayment('online_payment')">
                    <input type="radio" name="payment" value="online_payment">
                    <div class="d-flex align-items-center gap-3">
                      <div style="width:48px;height:48px;background:#1a1a1a;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;"><i class="bi bi-credit-card"></i></div>
                      <div>
                        <div class="fw-semibold">Online Payment</div>
                        <div class="text-muted" style="font-size:0.82rem;">Credit/Debit card via secure gateway.</div>
                      </div>
                      <i class="bi bi-check-circle-fill ms-auto text-success" id="chk-online_payment" style="display:none"></i>
                    </div>
                  </label>

                  <label class="payment-option" id="opt-cash_on_arrival" onclick="selectPayment('cash_on_arrival')">
                    <input type="radio" name="payment" value="cash_on_arrival">
                    <div class="d-flex align-items-center gap-3">
                      <div style="width:48px;height:48px;background:#2d7a4f;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;"><i class="bi bi-cash-stack"></i></div>
                      <div>
                        <div class="fw-semibold">Cash on Arrival</div>
                        <div class="text-muted" style="font-size:0.82rem;">Pay in cash when you check in. Subject to approval.</div>
                      </div>
                      <i class="bi bi-check-circle-fill ms-auto text-success" id="chk-cash_on_arrival" style="display:none"></i>
                    </div>
                  </label>
                </div>

                <!-- Payment Processing Simulation -->
                <div id="paymentSimPanel" class="mt-3 p-3 rounded" style="background:#f8f9fa;display:none;border-left:4px solid var(--gold);">
                  <div id="paySimMessage"></div>
                </div>

                <div class="d-flex gap-3 mt-4">
                  <button class="btn btn-outline-gold" onclick="goToStep(2)"><i class="bi bi-arrow-left me-1"></i>Back</button>
                  <button class="btn btn-gold flex-fill" onclick="showBookingSummary()">Review Booking</button>
                </div>
              </div>

              <!-- STEP 4: SUMMARY & CONFIRM -->
              <div class="step-panel" id="panel4">
                <h5 class="mb-4" style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;">Booking Summary</h5>
                <div id="summaryContent"></div>

                <div id="submitError" class="alert alert-danger mt-3" style="display:none;"></div>

                <div class="d-flex gap-3 mt-4">
                  <button class="btn btn-outline-gold" onclick="goToStep(3)"><i class="bi bi-arrow-left me-1"></i>Back</button>
                  <button class="btn btn-gold flex-fill" id="confirmBtn" onclick="submitBooking()">
                    <i class="bi bi-lock-fill me-2"></i>Confirm & Pay
                  </button>
                </div>
              </div>

            </div><!-- /booking-card -->
          </div><!-- /col -->

          <!-- SIDE SUMMARY -->
          <div class="col-lg-4">
            <div class="price-preview sticky-top" style="top:90px;">
              <div class="section-label mb-2" style="color:var(--gold-lt);">Your Selection</div>
              <div id="sideUnit" style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:#fff;margin-bottom:0.5rem;">—</div>
              <div id="sideDates" style="color:rgba(255,255,255,0.6);font-size:0.85rem;margin-bottom:1rem;">Select dates above</div>
              <hr style="border-color:rgba(255,255,255,0.15);">
              <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;color:rgba(255,255,255,0.7);">
                <span>Price per night</span>
                <span id="sidePriceNight">—</span>
              </div>
              <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;color:rgba(255,255,255,0.7);">
                <span>Total nights</span>
                <span id="sideNights">—</span>
              </div>
              <hr style="border-color:rgba(255,255,255,0.15);">
              <div class="d-flex justify-content-between">
                <span style="color:#fff;font-weight:600;">Total</span>
                <span id="sideTotal" style="color:var(--gold);font-size:1.3rem;font-family:'Cormorant Garamond',serif;">—</span>
              </div>
            </div>
          </div>
        </div><!-- /row -->
      </div>
    </div>
  </div>
</div>

<?php include '_footer.php'; ?>

<script>
var APP_URL = '<?php echo APP_URL; ?>';
var CSRF    = '<?php echo $csrfToken; ?>';
var bookingData = {};

function goToStep(n) {
  document.querySelectorAll('.step-panel').forEach(function(p) { p.classList.remove('active'); });
  document.getElementById('panel'+n).classList.add('active');
  document.querySelectorAll('.step-item').forEach(function(el,i) {
    el.classList.remove('active','done');
    if (i+1 < n) el.classList.add('done');
    if (i+1 === n) el.classList.add('active');
  });
  window.scrollTo({top:200,behavior:'smooth'});
}

async function checkAvailability() {
  var ci  = document.getElementById('checkIn').value;
  var co  = document.getElementById('checkOut').value;
  var uid = document.getElementById('unitTypeSelect').value;
  var res = document.getElementById('availResult');

  if (!ci||!co||!uid) { res.innerHTML='<div class="alert alert-warning">Please fill in all fields.</div>'; return; }
  res.innerHTML = '<div class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Checking availability...</div>';

  var form = new FormData();
  form.append('unit_type_id', uid);
  form.append('check_in',  ci);
  form.append('check_out', co);

  var r = await fetch(APP_URL+'/ajax/check-availability.php', {method:'POST', body:form});
  var d = await r.json();

  if (d.available) {
    res.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>'+d.message+'<br>'
      + '<strong>&#8369;'+Number(d.price_per_night).toLocaleString()+'/night &times; '+d.nights+' nights = &#8369;'+Number(d.total_price).toLocaleString()+'</strong></div>';

    bookingData = {
      unit_type_id: uid,
      check_in:  ci, check_out: co,
      nights: d.nights,
      price_per_night: d.price_per_night,
      total_price: d.total_price,
      unit_name: document.getElementById('unitTypeSelect').selectedOptions[0].dataset.name,
      max_guests: document.getElementById('unitTypeSelect').selectedOptions[0].dataset.max,
    };
    updateSide();
    setTimeout(function(){ goToStep(2); }, 800);
  } else {
    res.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>'+d.message+'</div>';
  }
}

function updateSide() {
  document.getElementById('sideUnit').textContent      = bookingData.unit_name || '—';
  document.getElementById('sideDates').textContent     = bookingData.check_in && bookingData.check_out
    ? bookingData.check_in + ' → ' + bookingData.check_out : 'Select dates';
  document.getElementById('sidePriceNight').textContent= bookingData.price_per_night ? '₱'+Number(bookingData.price_per_night).toLocaleString() : '—';
  document.getElementById('sideNights').textContent    = bookingData.nights || '—';
  document.getElementById('sideTotal').textContent     = bookingData.total_price ? '₱'+Number(bookingData.total_price).toLocaleString() : '—';
}

function validateGuestStep() {
  var name  = document.getElementById('fullName').value.trim();
  var email = document.getElementById('emailAddr').value.trim();
  var phone = document.getElementById('phoneNum').value.trim();
  var ng    = parseInt(document.getElementById('numGuests').value);

  if (!name)  { alert('Please enter your full name.'); return; }
  if (!email) { alert('Please enter a valid email.'); return; }
  if (!phone) { alert('Please enter your phone number.'); return; }
  if (!ng||ng<1) { alert('Please enter number of guests.'); return; }
  if (ng > parseInt(bookingData.max_guests)) {
    alert('Maximum guests for this unit is '+bookingData.max_guests+'.'); return;
  }
  bookingData.full_name   = name;
  bookingData.email       = email;
  bookingData.phone       = phone;
  bookingData.num_guests  = ng;
  bookingData.special_req = document.getElementById('specialReq').value.trim();
  goToStep(3);
}

var selectedPayment = null;
function selectPayment(method) {
  selectedPayment = method;
  ['gcash','online_payment','cash_on_arrival'].forEach(function(m) {
    document.getElementById('opt-'+m).classList.toggle('selected', m===method);
    document.getElementById('chk-'+m).style.display = m===method ? '' : 'none';
  });
  document.getElementById('paymentSimPanel').style.display = '';
  var msgs = {
    gcash:           '<i class="bi bi-phone me-2" style="color:var(--gold)"></i><strong>GCash Selected</strong> — You will be redirected to GCash to complete payment.',
    online_payment:  '<i class="bi bi-credit-card me-2" style="color:var(--gold)"></i><strong>Online Payment Selected</strong> — Secure card processing.',
    cash_on_arrival: '<i class="bi bi-cash-stack me-2" style="color:var(--gold)"></i><strong>Cash on Arrival Selected</strong> — Pay at check-in.',
  };
  document.getElementById('paySimMessage').innerHTML = msgs[method];
  bookingData.payment_method = method;
}

function showBookingSummary() {
  if (!selectedPayment) { alert('Please select a payment method.'); return; }
  var payLabels = {gcash:'GCash',online_payment:'Online Payment',cash_on_arrival:'Cash on Arrival'};
  document.getElementById('summaryContent').innerHTML =
    '<div class="table-responsive"><table class="table table-borderless" style="font-size:0.9rem;">'
    + '<tr><td class="text-muted">Unit Type</td><td class="fw-semibold">'+bookingData.unit_name+'</td></tr>'
    + '<tr><td class="text-muted">Check-in</td><td class="fw-semibold">'+bookingData.check_in+'</td></tr>'
    + '<tr><td class="text-muted">Check-out</td><td class="fw-semibold">'+bookingData.check_out+'</td></tr>'
    + '<tr><td class="text-muted">Total Nights</td><td class="fw-semibold">'+bookingData.nights+'</td></tr>'
    + '<tr><td class="text-muted">Price/Night</td><td class="fw-semibold">&#8369;'+Number(bookingData.price_per_night).toLocaleString()+'</td></tr>'
    + '<tr><td class="text-muted">Total Amount</td><td class="fw-semibold" style="color:var(--gold);font-size:1.1rem;">&#8369;'+Number(bookingData.total_price).toLocaleString()+'</td></tr>'
    + '<tr><td class="text-muted">Guest</td><td class="fw-semibold">'+bookingData.full_name+'</td></tr>'
    + '<tr><td class="text-muted">Email</td><td>'+bookingData.email+'</td></tr>'
    + '<tr><td class="text-muted">Payment</td><td class="fw-semibold">'+payLabels[bookingData.payment_method]+'</td></tr>'
    + '</table></div>';
  goToStep(4);
}

async function submitBooking() {
  var btn = document.getElementById('confirmBtn');
  var err = document.getElementById('submitError');
  err.style.display = 'none';
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

  if (bookingData.payment_method === 'gcash') {
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting to GCash...';
    await new Promise(function(r){ setTimeout(r, 2000); });
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Confirming payment...';
    await new Promise(function(r){ setTimeout(r, 1500); });
  } else if (bookingData.payment_method === 'online_payment') {
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing card...';
    await new Promise(function(r){ setTimeout(r, 2000); });
  }

  var form = new FormData();
  form.append('csrf_token',       CSRF);
  form.append('unit_type_id',     bookingData.unit_type_id);
  form.append('check_in_date',    bookingData.check_in);
  form.append('check_out_date',   bookingData.check_out);
  form.append('full_name',        bookingData.full_name);
  form.append('email',            bookingData.email);
  form.append('phone',            bookingData.phone);
  form.append('number_of_guests', bookingData.num_guests);
  form.append('special_requests', bookingData.special_req || '');
  form.append('payment_method',   bookingData.payment_method);

  var r = await fetch(APP_URL+'/ajax/submit-booking.php', {method:'POST', body:form});
  var d = await r.json();

  if (d.success) {
    window.location.href = d.redirect;
  } else {
    err.textContent = d.message;
    err.style.display = '';
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Confirm & Pay';
  }
}
</script>