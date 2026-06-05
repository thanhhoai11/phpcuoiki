<?php $pageTitle = 'Chi Tiết – ' . htmlspecialchars($room['type_name'] ?? 'Phòng'); ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

:root {
    --gold:      #C9A84C;
    --gold-dark: #9A7335;
    --ink:       #1A1A2E;
    --cream:     #FAF8F3;
    --muted:     #7A7A8A;
    --border:    #E8E4D8;
    --green:     #2A7A4F;
}
body { font-family: 'DM Sans', sans-serif; background: var(--cream); }

/* ── Breadcrumb ── */
.bc { font-size: .85rem; margin-bottom: 1.5rem; color: var(--muted); }
.bc a { color: var(--ink); text-decoration: none; }
.bc a:hover { text-decoration: underline; }
.bc .sep { margin: 0 .4rem; color: var(--muted); }

/* ══════════════════════════════════════════
   LEFT COLUMN
══════════════════════════════════════════ */
.detail-img {
    width: 100%;
    height: 320px;
    object-fit: cover;
    border-radius: 16px;
    display: block;
    margin-bottom: 1.8rem;
    box-shadow: 0 8px 32px rgba(26,26,46,.13);
}
.room-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.1rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: .9rem;
}
.info-line {
    display: flex; align-items: center; gap: 8px;
    font-size: .92rem; color: #333;
    margin-bottom: .4rem;
}
.info-line i { color: var(--gold); width: 18px; flex-shrink: 0; }
.block-heading {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700; font-size: .97rem;
    color: var(--ink);
    margin: 1.3rem 0 .45rem;
}
.desc-text { font-size: .9rem; color: #555; line-height: 1.7; margin: 0; }

/* amenities grid 2-col */
.am-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    row-gap: 2px; column-gap: 0;
}
.am-row {
    display: flex; align-items: center; gap: 7px;
    font-size: .88rem; color: #333;
    padding: 5px 0;
}
.am-row i { color: var(--green); font-size: .9rem; flex-shrink: 0; }

/* ══════════════════════════════════════════
   RIGHT COLUMN — Booking panel + Room selector
══════════════════════════════════════════ */
.right-sticky { position: sticky; top: 80px; }

/* ── Booking card ── */
.bk-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 20px rgba(26,26,46,.08);
    overflow: hidden;
    margin-bottom: 1.2rem;
}
.bk-header {
    background: var(--ink);
    color: #fff;
    padding: .9rem 1.4rem;
    display: flex; align-items: center; gap: 9px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600; font-size: 1rem;
}
.bk-body { padding: 1.3rem 1.4rem 1.4rem; }

.f-label {
    font-size: .65rem; font-weight: 600;
    letter-spacing: .13em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 5px; display: block;
}
.f-input {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    padding: 9px 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: .9rem; color: var(--ink);
    background: var(--cream);
    transition: border-color .2s, box-shadow .2s;
    appearance: auto;
}
.f-input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,168,76,.13);
    background: #fff;
}
.f-input[readonly] { cursor: default; background: #f5f5f5; }

/* ── Thêm từ detail.php: validate states ── */
.f-input.is-invalid {
    border-color: #C0392B !important;
    box-shadow: 0 0 0 3px rgba(192,57,43,.1) !important;
}
.f-input.is-valid {
    border-color: var(--green) !important;
    box-shadow: 0 0 0 3px rgba(42,122,79,.1) !important;
}
.date-error {
    font-size: .75rem;
    color: #C0392B;
    margin-top: 3px;
    min-height: 16px;
    display: flex;
    align-items: center;
    gap: 3px;
}

.btn-book {
    width: 100%;
    background: var(--ink);
    color: #fff; border: none;
    border-radius: 11px;
    padding: .82rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600; font-size: .96rem;
    letter-spacing: .03em;
    cursor: pointer;
    transition: opacity .18s, transform .15s;
    margin-top: .3rem;
    display: block;
    text-align: center;
    text-decoration: none;
}
.btn-book:hover:not(:disabled):not([style*="pointer-events: none"]) {
    opacity: .88; transform: translateY(-1px);
}
.btn-book:disabled { opacity: .5; cursor: not-allowed; }

.bk-hint {
    text-align: center;
    font-size: .77rem; color: var(--muted);
    margin-top: .55rem; margin-bottom: 0;
}

/* ── Room selector card ── */
.rs-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 20px rgba(26,26,46,.08);
    overflow: hidden;
}
.rs-header {
    background: var(--ink); color: #fff;
    padding: .9rem 1.4rem;
    display: flex; align-items: center; gap: 9px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600; font-size: 1rem;
}
.rs-body { padding: 1.1rem 1.4rem 1.3rem; }

/* Legend */
.rs-legend {
    display: flex; gap: 1rem; flex-wrap: wrap;
    margin-bottom: .9rem;
    font-size: .78rem; color: #555;
    font-family: 'DM Sans', sans-serif;
}
.rs-legend-item { display: flex; align-items: center; gap: 5px; }
.rs-dot {
    width: 13px; height: 13px; border-radius: 3px; border: 1.5px solid;
    flex-shrink: 0;
}
.rs-dot.avail  { background: #EEF2FF; border-color: #C5CFE8; }
.rs-dot.sel    { background: var(--ink); border-color: var(--ink); }
.rs-dot.taken  { background: #e9ecef; border-color: #ced4da; }

/* floor label */
.floor-lbl {
    font-size: .65rem; font-weight: 700;
    letter-spacing: .14em; text-transform: uppercase;
    color: var(--muted); margin: .85rem 0 .4rem;
}
.floor-lbl:first-of-type { margin-top: 0; }
.floor-rooms { display: flex; flex-wrap: wrap; gap: 0; }

/* room button */
.rm-btn {
    display: inline-flex; flex-direction: column;
    align-items: center; justify-content: center;
    width: 64px; height: 56px;
    border-radius: 9px;
    border: 1.5px solid #C5CFE8;
    background: #EEF2FF;
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    font-weight: 700; font-size: .88rem;
    cursor: pointer;
    transition: all .17s;
    margin: 3px;
}
.rm-btn .rm-sub { font-size: .57rem; font-weight: 400; opacity: .65; margin-top: 1px; }
.rm-btn:hover:not(.taken) {
    border-color: var(--ink); background: var(--ink); color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26,26,46,.2);
}
.rm-btn.selected {
    border-color: var(--ink); background: var(--ink); color: #fff;
    box-shadow: 0 4px 12px rgba(26,26,46,.2);
}
.rm-btn.taken {
    background: #e9ecef; border-color: #ced4da;
    color: #adb5bd; cursor: not-allowed; opacity: .65;
}

.rs-empty { font-size: .87rem; color: var(--muted); padding: .3rem 0; }

/* ── Thêm từ detail.php: Booking bar dính dưới ── */
.bk-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 300;
    background: var(--ink);
    color: #fff;
    padding: 12px 24px;
    display: none;
    border-top: 3px solid var(--gold);
    box-shadow: 0 -4px 20px rgba(0,0,0,.18);
}
.bk-bar.visible {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.bk-bar-labels { display: flex; flex-wrap: wrap; gap: 6px; }
.bk-label-badge {
    background: rgba(255,255,255,.15);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: .8rem;
    font-weight: 600;
}
.bk-capacity.ok   { color: #6ee7a0; font-size: .82rem; }
.bk-capacity.warn { color: #fbbf24; font-size: .82rem; }
.btn-bk-submit {
    background: linear-gradient(135deg, var(--gold-dark), var(--gold));
    color: #fff; border: none;
    border-radius: 9px;
    padding: 9px 24px;
    font-weight: 700; font-size: .93rem;
    cursor: pointer;
    transition: opacity .18s, transform .15s;
    white-space: nowrap;
}
.btn-bk-submit:disabled { opacity: .45; cursor: not-allowed; }
.btn-bk-submit:not(:disabled):hover { opacity: .88; transform: translateY(-1px); }
.btn-bk-clear {
    background: rgba(255,255,255,.12);
    color: #fff; border: none;
    border-radius: 9px;
    padding: 9px 16px;
    font-size: .85rem; cursor: pointer;
    transition: background .15s;
}
.btn-bk-clear:hover { background: rgba(255,255,255,.22); }
</style>

<!-- Breadcrumb -->
<nav class="bc">
    <a href="<?= BASE_URL ?>/?controller=home">Trang Chủ</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($room['type_name'] ?? '') ?></span>
</nav>

<?php
$roomImages = [
    'Phòng Đơn Tiêu Chuẩn'  => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=900&q=85',
    'Phòng Đôi Tiêu Chuẩn'  => 'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=900&q=85',
    'Phòng 3 Người (Triple)' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=900&q=85',
    'Phòng Gia Đình'         => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=900&q=85',
    'Phòng VIP Cao Cấp'      => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=900&q=85',
];
$heroImg = $roomImages[$room['type_name']] ?? 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=900&q=85';

// ── Thêm từ detail.php: tính số phòng cần dựa vào số khách ──
$people      = (int)($_GET['people'] ?? 1);
$maxGuests   = (int)($room['max_guests'] ?? 1);
$roomsNeeded = ($people > 0 && $maxGuests > 0) ? (int)ceil($people / $maxGuests) : 1;
$multiRoomMode = $roomsNeeded > 1;

// Group rooms by floor
$byFloor = [];
foreach (($allRoomsOfType ?? []) as $r) {
    $byFloor[(int)$r['floor']][] = $r;
}
ksort($byFloor);
?>

<div class="row g-4 align-items-start">

    <!-- ══ LEFT ══ -->
    <div class="col-lg-7">

        <img src="<?= $heroImg ?>" class="detail-img"
             alt="<?= htmlspecialchars($room['type_name']) ?>">

        <h1 class="room-title"><?= htmlspecialchars($room['type_name']) ?></h1>

        <div class="info-line">
            <i class="bi bi-people-fill"></i>
            <span><strong>Sức chứa tối đa:</strong> <?= $room['max_guests'] ?> người</span>
        </div>
        <div class="info-line">
            <i class="bi bi-currency-dollar"></i>
            <span><strong>Giá:</strong> <?= number_format($room['price'], 0, ',', '.') ?> VNĐ/đêm</span>
        </div>

        <div class="block-heading">Mô tả:</div>
        <p class="desc-text"><?= htmlspecialchars($room['description'] ?? '') ?></p>

        <?php if (!empty($room['amenities'])): ?>
        <div class="block-heading">Tiện nghi:</div>
        <div class="am-grid">
            <?php foreach ($room['amenities'] as $a): ?>
            <div class="am-row">
                <i class="bi bi-check2"></i>
                <?= htmlspecialchars($a['amenity_name']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>

    <!-- ══ RIGHT ══ -->
    <div class="col-lg-5">
        <div class="right-sticky">

            <!-- Booking card -->
            <div class="bk-card">
                <div class="bk-header">
                    <i class="bi bi-calendar-check"></i>
                    Đặt Phòng Nhanh
                </div>
                <div class="bk-body">

                    <div class="row g-2 mb-2">
                        <!-- Ngày nhận – thêm id và date-error từ detail.php -->
                        <div class="col-6">
                            <label class="f-label">Ngày Nhận</label>
                            <input type="date" id="bkCheckIn" class="f-input"
                                   min="<?= date('Y-m-d') ?>" required
                                   value="<?= htmlspecialchars($_GET['check_in'] ?? '') ?>">
                            <div class="date-error" id="errCheckIn"></div>
                        </div>
                        <!-- Ngày trả -->
                        <div class="col-6">
                            <label class="f-label">Ngày Trả</label>
                            <input type="date" id="bkCheckOut" class="f-input"
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required
                                   value="<?= htmlspecialchars($_GET['check_out'] ?? '') ?>">
                            <div class="date-error" id="errCheckOut"></div>
                        </div>
                        <!-- Số khách -->
                        <div class="col-6">
                            <label class="f-label">Số Khách</label>
                            <input type="number" id="bkPeople" class="f-input"
                                   min="1" value="<?= $people ?>">
                        </div>
                        <!-- Thêm từ detail.php: ô Số đêm (thay cho "Phòng đã chọn") -->
                        <div class="col-6">
                            <label class="f-label">Số Đêm</label>
                            <input type="text" id="bkNights" class="f-input"
                                   placeholder="–" readonly>
                        </div>
                    </div>

                    <!-- Thêm từ detail.php: thông báo multi-room -->
                    <div id="multiRoomNotice"
                         style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;
                                padding:8px 12px;font-size:.82rem;margin-bottom:10px;
                                <?= $multiRoomMode ? '' : 'display:none;' ?>">
                        <i class="bi bi-people-fill" style="color:#f59e0b;"></i>
                        Với <span id="noticeGuests"><?= $people ?></span> khách, bạn cần chọn
                        ít nhất <strong id="roomsNeededLabel"><?= $roomsNeeded ?></strong> phòng.
                    </div>

                    <!-- ═══════════════════════════════════════
                         NÚT ĐẶT NGAY — GIỮ KIỂM TRA ĐĂNG NHẬP
                         (từ detail_sau.php) + logic multi-room
                    ═══════════════════════════════════════ -->
                    <?php if (empty($_SESSION['user_id'])): ?>
                        <!-- Chưa đăng nhập: Nút vẫn cho phép nhấn để kích hoạt luồng lưu dữ liệu -->
                        <button type="button" class="btn-book" id="btnBook" onclick="submitBooking()">
                            <i class="bi bi-lock me-1"></i>Đăng nhập để đặt phòng
                        </button>
                        <p class="bk-hint">
                            Vui lòng chọn ngày và phòng để tiếp tục
                        </p>
                    <?php else: ?>
                        <!-- Đã đăng nhập: nút bình thường, disabled cho đến khi đủ điều kiện -->
                        <button type="button" class="btn-book" id="btnBook"
                                disabled onclick="submitBooking()">
                            <i class="bi bi-calendar-plus me-1"></i>Đặt Ngay
                        </button>
                        <p class="bk-hint" id="bkHint">
                            <?= $multiRoomMode
                                ? "← Chọn đủ $roomsNeeded phòng bên dưới"
                                : '← Vui lòng chọn một phòng bên dưới' ?>
                        </p>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Room selector -->
            <div class="rs-card">
                <div class="rs-header">
                    <i class="bi bi-grid-3x3-gap"></i>
                    Chọn Phòng
                    <?php if ($multiRoomMode): ?>
                    <span style="font-size:.75rem;opacity:.75;margin-left:6px;">
                        (chọn <?= $roomsNeeded ?> phòng)
                    </span>
                    <?php endif; ?>
                </div>
                <div class="rs-body">

                    <div class="rs-legend">
                        <div class="rs-legend-item">
                            <div class="rs-dot avail"></div><span>Còn trống</span>
                        </div>
                        <div class="rs-legend-item">
                            <div class="rs-dot sel"></div><span>Đang chọn</span>
                        </div>
                        <div class="rs-legend-item">
                            <div class="rs-dot taken"></div><span>Đã đặt</span>
                        </div>
                    </div>

                    <?php
                    $datesSelected = !empty($checkIn) && !empty($checkOut);
                    $byFloorDisplay = [];
                    // Gom nhóm lại để hiển thị (không lọc bỏ phòng đã đặt để hiển thị trạng thái 'Đã đặt')
                    foreach ($byFloor as $floor => $floorRooms) {
                        $byFloorDisplay[$floor] = $floorRooms;
                    }
                    ?>
                    <?php if (empty($byFloorDisplay)): ?>
                        <p class="rs-empty">Chưa có phòng nào cho loại này.</p>
                    <?php else: ?>
                        <?php foreach ($byFloorDisplay as $floor => $floorRooms): ?>
                        <div class="floor-lbl">Tầng <?= $floor ?></div>
                        <div class="floor-rooms">
                            <?php foreach ($floorRooms as $r):
                                // Phòng bị coi là 'đã đặt' nếu nó nằm trong danh sách bookedRoomIds (khi đã chọn ngày)
                                $taken = in_array($r['id'], $bookedRoomIds ?? []);
                            ?>
                            <button type="button"
                                    class="rm-btn <?= $taken ? 'taken' : '' ?>"
                                    data-room-id="<?= $r['id'] ?>"
                                    data-room-number="<?= htmlspecialchars($r['room_number']) ?>"
                                    <?= $taken ? 'disabled title="Phòng đã được đặt"' : 'onclick="toggleRoom(this)"' ?>>
                                <?= htmlspecialchars($r['room_number']) ?>
                                <span class="rm-sub"><?= $taken ? 'Đã đặt' : 'Trống' ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>

        </div><!-- /sticky -->
    </div><!-- /right col -->

</div><!-- /row -->

<!-- ═══════════════════════════════════════════════
     Thêm từ detail.php: Booking bar dính dưới
     (chỉ hiện khi đã đăng nhập + đã chọn ≥ 1 phòng)
═══════════════════════════════════════════════ -->
<?php if (!empty($_SESSION['user_id'])): ?>
<div class="bk-bar" id="bkBar">
    <div>
        <div style="font-weight:700;font-size:.95rem;margin-bottom:4px;">
            Đã chọn <span id="bkBarCount">0</span> phòng
            <span id="bkBarMin" style="font-size:.8rem;opacity:.75;">
                (tối thiểu <span id="bkBarNeeded"><?= $roomsNeeded ?></span>)
            </span>
            <span id="bkBarTotal" style="color:var(--gold);margin-left:10px;"></span>
        </div>
        <div class="bk-bar-labels" id="bkBarLabels"></div>
        <div class="bk-capacity" id="bkBarCapacity"></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <button class="btn-bk-clear" onclick="clearRooms()">
            <i class="bi bi-x-circle me-1"></i>Bỏ chọn
        </button>
        <button class="btn-bk-submit" id="btnBarSubmit" disabled onclick="submitBooking()">
            <i class="bi bi-calendar-check me-1"></i>Đặt Phòng Đã Chọn
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Hidden form để submit (từ detail.php) -->
<form method="GET" action="<?= BASE_URL ?>/" id="bookingForm" style="display:none;">
    <input type="hidden" name="controller" value="booking">
    <input type="hidden" name="action"     value="create">
    <input type="hidden" name="check_in"   id="formCheckIn"  value="">
    <input type="hidden" name="check_out"  id="formCheckOut" value="">
    <input type="hidden" name="people"     id="formPeople"   value="<?= $people ?>">
    <div id="formRoomIds"></div>
</form>

<!-- Thêm từ detail.php: Modal cảnh báo sức chứa -->
<div class="modal fade" id="capacityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    Chưa đủ sức chứa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="capacityModalMsg"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Quay lại chọn phòng</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════ -->
<script>
// ═══════════════════════════════════════════════
// 1. KHAI BÁO & BIẾN TOÀN CỤC
// ═══════════════════════════════════════════════
const IS_LOGGED_IN = <?= empty($_SESSION['user_id']) ? 'false' : 'true' ?>;
const TODAY        = '<?= date('Y-m-d') ?>';
const inEl         = document.getElementById('bkCheckIn');
const outEl        = document.getElementById('bkCheckOut');
const peopleInput  = document.getElementById('bkPeople');

let   ROOMS_NEEDED = <?= $roomsNeeded ?>;
const MAX_GUESTS   = <?= $maxGuests ?>;
const PRICE        = <?= (float)$room['price'] ?>;

const selectedRooms = new Map(); // roomId → roomNumber

// ═══════════════════════════════════════════════
// 2. XỬ LÝ NGÀY THÁNG & SỐ ĐÊM
// ═══════════════════════════════════════════════
function markInvalid(el) { el.classList.remove('is-valid');   el.classList.add('is-invalid'); }
function markValid(el)   { el.classList.remove('is-invalid'); el.classList.add('is-valid');   }
function showErr(id, msg) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${msg}`;
}
function hideErr(id) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = '';
}

function calcNightsNum() {
    if (inEl && outEl && inEl.value && outEl.value && outEl.value > inEl.value) {
        return Math.round((new Date(outEl.value) - new Date(inEl.value)) / 86400000);
    }
    return 0;
}

function calcNights() {
    const ni = document.getElementById('bkNights');
    const n  = calcNightsNum();
    if (ni) ni.value = n > 0 ? n + ' đêm' : '–';
    updateBookBtn();
}

function syncOutMin() {
    if (!inEl || !inEl.value) return;
    const next = new Date(inEl.value);
    next.setDate(next.getDate() + 1);
    if (outEl) {
        outEl.min = next.toISOString().split('T')[0];
        if (outEl.value && outEl.value <= inEl.value) {
            outEl.value = '';
            markInvalid(outEl);
            showErr('errCheckOut', 'Ngày trả phòng phải sau ngày nhận phòng.');
        }
    }
    calcNights();
}

if (inEl) {
    inEl.addEventListener('change', function () {
        if (!inEl.value) {
            markInvalid(inEl); showErr('errCheckIn', 'Vui lòng chọn ngày nhận phòng.'); return;
        }
        if (inEl.value < TODAY) {
            markInvalid(inEl); showErr('errCheckIn', 'Không thể chọn ngày trong quá khứ.'); return;
        }
        markValid(inEl); hideErr('errCheckIn');
        syncOutMin();
    });
}

if (outEl) {
    outEl.addEventListener('change', function () {
        if (!outEl.value) {
            markInvalid(outEl); showErr('errCheckOut', 'Vui lòng chọn ngày trả phòng.'); return;
        }
        if (outEl.value <= inEl.value) {
            outEl.value = '';
            markInvalid(outEl); showErr('errCheckOut', 'Ngày trả phòng phải sau ngày nhận phòng.'); return;
        }
        markValid(outEl); hideErr('errCheckOut');
        calcNights();

        // Reload để server tính lại phòng trống
        const people = peopleInput ? peopleInput.value : 1;
        const params = new URLSearchParams(window.location.search);
        params.set('check_in',  inEl.value);
        params.set('check_out', outEl.value);
        params.set('people',    people);
        window.location.search = params.toString();
    });
}

// ═══════════════════════════════════════════════
// 3. CHỌN PHÒNG & VALIDATE
// ═══════════════════════════════════════════════
window.toggleRoom = function(btn) {
    const id  = btn.dataset.roomId;
    const num = btn.dataset.roomNumber;

    if (btn.classList.contains('selected')) {
        btn.classList.remove('selected');
        const sub = btn.querySelector('.rm-sub');
        if (sub) sub.textContent = 'Trống';
        selectedRooms.delete(id);
    } else {
        btn.classList.add('selected');
        const sub = btn.querySelector('.rm-sub');
        if (sub) sub.textContent = 'Đã chọn';
        selectedRooms.set(id, num);
    }
    updateBookBtn();
    if (typeof updateBar === 'function') updateBar();
};

window.clearRooms = function() {
    selectedRooms.clear();
    document.querySelectorAll('.rm-btn.selected').forEach(b => {
        b.classList.remove('selected');
        const sub = b.querySelector('.rm-sub');
        if (sub) sub.textContent = 'Trống';
    });
    updateBookBtn();
    if (typeof updateBar === 'function') updateBar();
};

function updateBookBtn() {
    const ready = selectedRooms.size >= ROOMS_NEEDED
                  && inEl.value && outEl.value
                  && outEl.value > inEl.value;
    const btn = document.getElementById('btnBook');
    if (btn) btn.disabled = !ready;
    const barBtn = document.getElementById('btnBarSubmit');
    if (barBtn) barBtn.disabled = !ready;
}

if (peopleInput) {
    peopleInput.addEventListener('input', function () {
        const newPeople = Math.max(1, parseInt(this.value) || 1);
        ROOMS_NEEDED = Math.ceil(newPeople / MAX_GUESTS);

        const barNeeded = document.getElementById('bkBarNeeded');
        if (barNeeded) barNeeded.textContent = ROOMS_NEEDED;

        const lbl = document.getElementById('roomsNeededLabel');
        if (lbl) lbl.textContent = ROOMS_NEEDED;

        const noticeGuests = document.getElementById('noticeGuests');
        if (noticeGuests) noticeGuests.textContent = newPeople;

        const notice = document.getElementById('multiRoomNotice');
        if (notice) notice.style.display = ROOMS_NEEDED > 1 ? '' : 'none';

        const hint = document.getElementById('bkHint');
        if (hint) {
            hint.textContent = ROOMS_NEEDED === 1
                ? '← Vui lòng chọn một phòng bên dưới'
                : `← Chọn đủ ${ROOMS_NEEDED} phòng bên dưới`;
        }
        clearRooms();
    });
}

// ═══════════════════════════════════════════════
// 4. SUBMIT ĐẶT PHÒNG
// ═══════════════════════════════════════════════
window.submitBooking = function() {
    if (!inEl.value) {
        markInvalid(inEl); showErr('errCheckIn', 'Vui lòng chọn ngày nhận phòng.'); return;
    }
    if (!outEl.value || outEl.value <= inEl.value) {
        markInvalid(outEl); showErr('errCheckOut', 'Ngày trả phòng phải sau ngày nhận phòng.'); return;
    }
    if (selectedRooms.size === 0) {
        alert('Vui lòng chọn ít nhất một phòng.'); return;
    }

    const totalCap = selectedRooms.size * MAX_GUESTS;
    const needed   = parseInt(peopleInput.value) || 1;
    if (totalCap < needed && selectedRooms.size < ROOMS_NEEDED) {
        const msg = `Tổng sức chứa ${totalCap} khách chưa đủ cho ${needed} khách. Vui lòng chọn thêm phòng.`;
        const modalMsg = document.getElementById('capacityModalMsg');
        if (modalMsg) {
            modalMsg.innerHTML = msg;
            new bootstrap.Modal(document.getElementById('capacityModal')).show();
        } else {
            alert(msg);
        }
        return;
    }

    document.getElementById('formCheckIn').value  = inEl.value;
    document.getElementById('formCheckOut').value = outEl.value;
    document.getElementById('formPeople').value   = peopleInput.value;

    const formRoomIds = document.getElementById('formRoomIds');
    formRoomIds.innerHTML = '';
    selectedRooms.forEach((num, id) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'room_ids[]'; inp.value = id;
        formRoomIds.appendChild(inp);
    });

    document.getElementById('bookingForm').submit();
};

// ═══════════════════════════════════════════════
// 5. KHỞI TẠO BAN ĐẦU & LOGGED IN ONLY
// ═══════════════════════════════════════════════
if (inEl && inEl.value) syncOutMin();
if (inEl && outEl && inEl.value && outEl.value) calcNights();
updateBookBtn();

if (IS_LOGGED_IN) {
    window.updateBar = function() {
        const count  = selectedRooms.size;
        const bar    = document.getElementById('bkBar');
        if (!bar) return;

        if (count === 0) {
            bar.classList.remove('visible');
            document.body.style.paddingBottom = '0';
            return;
        }

        bar.classList.add('visible');
        document.body.style.paddingBottom = '80px';

        const nights   = calcNightsNum();
        const total    = count * nights * PRICE;
        const capacity = count * MAX_GUESTS;

        document.getElementById('bkBarCount').textContent = count;

        const labelsEl = document.getElementById('bkBarLabels');
        if (labelsEl) {
            labelsEl.innerHTML = '';
            selectedRooms.forEach(num => {
                const span = document.createElement('span');
                span.className   = 'bk-label-badge';
                span.textContent = 'Phòng ' + num;
                labelsEl.appendChild(span);
            });
        }

        const totalEl = document.getElementById('bkBarTotal');
        if (totalEl) {
            totalEl.textContent = nights > 0 ? total.toLocaleString('vi-VN') + ' VNĐ (' + nights + ' đêm)' : '';
        }

        const capEl  = document.getElementById('bkBarCapacity');
        const needed = parseInt(peopleInput.value) || 1;
        if (capEl) {
            if (ROOMS_NEEDED > 1) {
                const enough = capacity >= needed;
                capEl.className = 'bk-capacity ' + (enough ? 'ok' : 'warn');
                capEl.innerHTML = enough
                    ? `<i class="bi bi-check-circle-fill me-1"></i>Sức chứa: ${capacity}/${needed} khách ✓`
                    : `<i class="bi bi-exclamation-triangle-fill me-1"></i>Sức chứa: ${capacity}/${needed} khách — cần thêm ${needed - capacity} chỗ`;
            } else {
                capEl.innerHTML = '';
            }
        }
    };
}
</script>