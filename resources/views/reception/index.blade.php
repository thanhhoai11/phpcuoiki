<?php
// Extract unique floors, types, and counts
$allFloors = array_keys($floors);
sort($allFloors);

$uniqueTypes = [];
$uniqueCapacities = [];
$counts = ['available' => 0, 'soon_to_checkin' => 0, 'occupied' => 0, 'soon_to_checkout' => 0];

foreach ($floors as $floor => $rooms) {
    foreach ($rooms as $room) {
        $uniqueTypes[$room['room_type_id']] = $room['type_name'];
        $uniqueCapacities[] = $room['max_guests'];
        if (isset($counts[$room['status']])) {
            $counts[$room['status']]++;
        }
    }
}
asort($uniqueTypes);
$uniqueCapacities = array_unique($uniqueCapacities);
sort($uniqueCapacities);
?>
<style>
    /* Status Colors */
    .status-available { background: #e2f4e8; border: 1px solid #c3ebd2; }
    .status-available .room-icon { color: #2d9f58; }
    
    .status-soon_to_checkin { background: #fff4ce; border: 1px solid #ffe8a1; }
    .status-soon_to_checkin .room-icon { color: #d44b25; }
    
    .status-occupied { background: #e6efff; border: 1px solid #cce0ff; }
    .status-occupied .room-icon { color: #2b6ff2; }
    
    .status-soon_to_checkout { background: #ffe5e5; border: 1px solid #ffcccc; }
    .status-soon_to_checkout .room-icon { color: #dc3545; }

    /* Room Card */
    .room-card {
        border-radius: 12px;
        padding: 18px 15px;
        position: relative;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        height: 108px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .room-card:hover { 
        transform: translateY(-4px); 
        box-shadow: 0 8px 16px rgba(0,0,0,0.06); 
    }
    .room-card.selected { 
        border: 2px solid #0d6efd !important; 
        box-shadow: 0 0 0 4px rgba(13,110,253,0.15); 
    }
    
    .room-number { font-size: 1.35rem; font-weight: 700; color: #1e293b; margin: 0; }
    .room-status-text { font-size: 0.8rem; font-weight: 600; margin: 2px 0 0 0; }
    .room-capacity { font-size: 0.75rem; color: #64748b; margin: 0; font-weight: 500; }
    .room-icon { position: absolute; top: 18px; right: 15px; font-size: 1.25rem; }

    /* Filter Bar */
    .filter-card { 
        background: white; 
        padding: 16px 20px; 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.01);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        align-items: flex-end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filter-label { 
        font-size: 0.775rem; 
        color: #475569; 
        font-weight: 600; 
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .filter-card select, .filter-card input { 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        padding: 8px 12px; 
        outline: none; 
        font-size: 0.875rem;
        color: #334155;
        background-color: #fff;
        transition: border-color 0.15s;
    }
    .filter-card select:focus, .filter-card input:focus {
        border-color: #0d6efd;
    }
    
    /* Interactive Legend Row */
    .legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
    }
    .legend-badge {
        padding: 10px 16px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        border: 1px solid transparent;
        user-select: none;
    }
    .legend-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .legend-badge.active-filter {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.12) !important;
        font-weight: 700;
    }
    .legend-badge .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    /* Right Panel Details & Layout fix */
    .detail-img { width: 100%; height: 180px; object-fit: cover; border-radius: 12px; margin-bottom: 20px; background: #eee; }
    .detail-row { display: flex; margin-bottom: 12px; font-size: 0.9rem; }
    .detail-label { width: 110px; color: #64748b; display: flex; align-items: center; gap: 8px; font-weight: 500; }
    .detail-value { flex: 1; font-weight: 600; color: #1e293b; }
    .btn-action { border-radius: 8px; padding: 10px; font-weight: 600; width: 100%; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 0.875rem;}

    .empty-panel-flex {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
</style>

<!-- Main Center Column -->
<main class="main-content">
    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-dark"><?= "S\u{01a1} \u{0111}\u{1ed3} tr\u{1ea1}ng th\u{00e1}i ph\u{00f2}ng" ?></h4>
            <p class="text-muted m-0" style="font-size:0.85rem;"><?= "Qu\u{1ea3}n l\u{00fd} v\u{00e0} c\u{1ead}p nh\u{1ead}t s\u{01a1} \u{0111}\u{1ed3} \u{0111}\u{1eb7}t ph\u{00f2}ng th\u{1edd}i gian th\u{1ef1}c" ?></p>
        </div>
        <div class="input-group shadow-sm" style="width: 320px; border-radius: 8px; overflow: hidden;">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
            <input type="text" id="search-input" class="form-control border-start-0 py-2" placeholder="<?= "T\u{00ec}m s\u{1ed1} ph\u{00f2}ng, lo\u{1ea1}i ph\u{00f2}ng..." ?>" oninput="filterRooms()">
        </div>
    </div>

    <!-- Interactive Legend Box -->
    <div class="legend-row">
        <div class="legend-badge status-available" data-status-val="available" onclick="toggleLegendFilter(this)">
            <span class="dot" style="background: #2d9f58;"></span> <?= "\u{0110}ang tr\u{1ed1}ng" ?> (<span id="count-available"><?= $counts['available'] ?></span>)
        </div>
        <div class="legend-badge status-soon_to_checkin" data-status-val="soon_to_checkin" onclick="toggleLegendFilter(this)">
            <span class="dot" style="background: #d44b25;"></span> <?= "S\u{1eaf}p nh\u{1ead}n" ?> (<span id="count-soon_to_checkin"><?= $counts['soon_to_checkin'] ?></span>)
        </div>
        <div class="legend-badge status-occupied" data-status-val="occupied" onclick="toggleLegendFilter(this)">
            <span class="dot" style="background: #2b6ff2;"></span> <?= "\u{0110}ang s\u{1eed} d\u{1ee5}ng" ?> (<span id="count-occupied"><?= $counts['occupied'] ?></span>)
        </div>
        <div class="legend-badge status-soon_to_checkout" data-status-val="soon_to_checkout" onclick="toggleLegendFilter(this)">
            <span class="dot" style="background: #dc3545;"></span> <?= "S\u{1eaf}p tr\u{1ea3}" ?> (<span id="count-soon_to_checkout"><?= $counts['soon_to_checkout'] ?></span>)
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="filter-card">
        <div class="filter-grid">
            <div class="filter-group">
                <span class="filter-label"><?= "T\u{1ea7}ng" ?></span>
                <select id="filter-floor" onchange="filterRooms()">
                    <option value="Tất cả"><?= "T\u{1ea5}t c\u{1ea3} t\u{1ea7}ng" ?></option>
                    <?php foreach ($allFloors as $fl): ?>
                        <option value="<?= $fl ?>"><?= "T\u{1ea7}ng" ?> <?= $fl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label"><?= "Lo\u{1ea1}i ph\u{00f2}ng" ?></span>
                <select id="filter-type" onchange="filterRooms()">
                    <option value="Tất cả"><?= "T\u{1ea5}t c\u{1ea3} lo\u{1ea1}i ph\u{00f2}ng" ?></option>
                    <?php foreach ($uniqueTypes as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label"><?= "S\u{1ee9}c ch\u{1ee9}a" ?></span>
                <select id="filter-guests" onchange="filterRooms()">
                    <option value="Tất cả"><?= "T\u{1ea5}t c\u{1ea3} s\u{1ee9}c ch\u{1ee9}a" ?></option>
                    <?php foreach ($uniqueCapacities as $cap): ?>
                        <option value="<?= $cap ?>"><?= $cap ?> <?= "ng\u{01b0}\u{1edd}i" ?></option>
                    <?php endforeach; ?>
                    <option value="5+">5+ <?= "ng\u{01b0}\u{1edd}i" ?></option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label"><?= "Tr\u{1ea1}ng th\u{00e1}i" ?></span>
                <select id="filter-status" onchange="filterRooms()">
                    <option value="Tất cả"><?= "T\u{1ea5}t c\u{1ea3} tr\u{1ea1}ng th\u{00e1}i" ?></option>
                    <option value="available"><?= "\u{0110}ang tr\u{1ed1}ng" ?></option>
                    <option value="soon_to_checkin"><?= "S\u{1eaf}p nh\u{1ead}n" ?></option>
                    <option value="occupied"><?= "\u{0110}ang s\u{1eed} d\u{1ee5}ng" ?></option>
                    <option value="soon_to_checkout"><?= "S\u{1eaf}p tr\u{1ea3}" ?></option>
                </select>
            </div>

            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-1 py-2" style="border-radius: 8px; height: 38px;" onclick="resetFilters()">
                <i class="fa-solid fa-rotate-right"></i> <?= "L\u{00e0}m m\u{1edb}i" ?>
            </button>
        </div>
    </div>

    <!-- Floors & Rooms Grid -->
    <div id="room-grid-container">
        <?php foreach ($floors as $floor => $rooms): ?>
            <div class="floor-section mb-4" data-floor-num="<?= $floor ?>">
                <h6 class="fw-bold text-dark mb-3 mt-2"><i class="fa-solid fa-layer-group text-primary me-2"></i> <?= "T\u{1ea7}ng" ?> <?= $floor ?></h6>
                <div class="row g-3">
                    <?php foreach ($rooms as $room): 
                        $icon = 'fa-door-open';
                        $statusText = '';
                        if ($room['status'] == 'soon_to_checkout') { $icon = 'fa-bell-concierge'; $statusText = 'Sắp trả'; }
                        elseif ($room['status'] == 'soon_to_checkin') { $icon = 'fa-calendar-day'; $statusText = 'Sắp nhận'; }
                        elseif ($room['status'] == 'occupied') { $icon = 'fa-user-check'; $statusText = 'Đang sử dụng'; }
                        elseif ($room['status'] == 'available') { $icon = 'fa-door-open'; $statusText = 'Đang trống'; }
                        else { $statusText = $room['type_name']; }
                        
                        $roomJson = htmlspecialchars(json_encode($room));
                    ?>
                    <div class="col-md-4 col-sm-6 col-xl-2 col-xxl-2 room-card-wrapper">
                        <div class="room-card status-<?= $room['status'] ?>" 
                             data-floor="<?= $room['floor'] ?>"
                             data-type="<?= $room['room_type_id'] ?>"
                             data-guests="<?= $room['max_guests'] ?>"
                             data-status="<?= $room['status'] ?>"
                             data-search="<?= htmlspecialchars(strtolower($room['room_number'] . ' ' . $room['type_name'])) ?>"
                             id="room-card-<?= $room['id'] ?>"
                             onclick="selectRoom(this, <?= $roomJson ?>)">
                            
                            <i class="fa-solid <?= $icon ?> room-icon"></i>
                            <div>
                                <h5 class="room-number"><?= htmlspecialchars($room['room_number']) ?></h5>
                                <p class="room-status-text"><?= htmlspecialchars($statusText) ?></p>
                            </div>
                            <p class="room-capacity"><?= $room['max_guests'] ?> <?= "ng\u{01b0}\u{1edd}i" ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Right Sidebar (Details) -->
<aside class="right-panel shadow-sm" id="room-detail-panel" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold m-0" id="detail-title"><?= "Th\u{00f4}ng tin ph\u{00f2}ng" ?></h5>
        <button type="button" class="btn-close" onclick="closeDetailPanel()"></button>
    </div>
    
    <img src="" class="detail-img" id="detail-img" alt="Room Image">

    <div class="detail-row">
        <div class="detail-label"><i class="fa-solid fa-bed text-muted"></i> <?= "Lo\u{1ea1}i ph\u{00f2}ng" ?></div>
        <div class="detail-value" id="detail-type">---</div>
    </div>
    <div class="detail-row">
        <div class="detail-label"><i class="fa-solid fa-users text-muted"></i> <?= "S\u{1ee9}c ch\u{1ee9}a" ?></div>
        <div class="detail-value" id="detail-capacity">--- <?= "ng\u{01b0}\u{1edd}i" ?></div>
    </div>
    <div class="detail-row">
        <div class="detail-label"><i class="fa-solid fa-circle-dollar-to-slot text-muted"></i> <?= "Gi\u{00e1} ph\u{00f2}ng" ?></div>
        <div class="detail-value text-primary fs-5" id="detail-price">--- <?= "d" ?> / <?= "dem" ?></div>
    </div>
    <div class="detail-row">
        <div class="detail-label"><i class="fa-solid fa-circle-info text-muted"></i> <?= "Tr\u{1ea1}ng th\u{00e1}i" ?></div>
        <div class="detail-value" id="detail-status-badge">
            <span class="badge rounded-pill px-3">---</span>
        </div>
    </div>
    <div class="detail-row mb-4">
        <div class="detail-label"><i class="fa-solid fa-wifi text-muted"></i> <?= "Ti\u{1ec7}n \u{00ed}ch" ?></div>
        <div class="detail-value" id="detail-amenities" style="font-size: 0.825rem; line-height: 1.4; font-weight: 500; color: #475569;">---</div>
    </div>
    
    <hr class="my-3 text-muted">

    <!-- Operational Business Buttons -->
    <div class="mt-2">
        <h6 class="fw-bold mb-3 text-dark" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;"><?= "Nghi\u{1ec7}p v\u{1ee5} ph\u{00f2}ng" ?></h6>
        <div class="d-grid gap-2">
            <button class="btn btn-outline-success btn-action" id="btn-action-available" onclick="triggerStatusUpdate('available')">
                <i class="fa-solid fa-door-open"></i> <?= "Tr\u{1ea3} ph\u{00f2}ng (\u{0110}ang tr\u{1ed1}ng)" ?>
            </button>
            <button class="btn btn-outline-warning text-dark btn-action" id="btn-action-soon_to_checkin" onclick="triggerStatusUpdate('soon_to_checkin')">
                <i class="fa-solid fa-calendar-day"></i> <?= "\u{0110}\u{1eb7}t ph\u{00f2}ng (S\u{1eaf}p nh\u{1ead}n)" ?>
            </button>
            <button class="btn btn-outline-primary btn-action" id="btn-action-occupied" onclick="triggerStatusUpdate('occupied')">
                <i class="fa-solid fa-user-check"></i> <?= "Check-in (\u{0110}ang s\u{1eed} d\u{1ee5}ng)" ?>
            </button>
        </div>
    </div>
</aside>

<!-- Right Sidebar Placeholder (No d-flex/d-none bootstrap classes to avoid override bug) -->
<div class="right-panel empty-panel-flex text-muted shadow-sm" id="empty-detail-panel">
    <div class="bg-light p-4 rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
        <i class="fa-solid fa-bed fs-1 text-secondary" style="opacity: 0.5;"></i>
    </div>
    <h6 class="fw-bold text-dark"><?= "Ch\u{01b0}a ch\u{1ecd}n ph\u{00f2}ng" ?></h6>
    <p class="text-center px-4 fs-7 text-muted" style="font-size: 0.8rem;"><?= "H\u{00e3}y ch\u{1ecd}n m\u{1ed9}t ph\u{00f2}ng b\u{1ea5}t k\u{1ef3} tr\u{00ea}n s\u{01a1} \u{0111}\u{1ed3} \u{0111}\u{1ec3} xem th\u{00f4}ng tin chi ti\u{1ebf}t v\u{00e0} thao t\u{00e1}c nghi\u{1ec7}p v\u{1ee5} nhanh." ?></p>
</div>

<!-- Custom Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="statusToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage" style="font-weight: 500;">
                <?= "C\u{1ead}p nh\u{1ead}t th\u{00e0}nh c\u{00f4}ng!" ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Global variable to store active selected room info
    let selectedRoomId = null;
    let selectedRoomData = null;

    // Room Image Mapping from booking pages
    const roomImages = {
        'Ph\u00f2ng \u0110\u01a1n Ti\u00eau Chu\u1ea9n': 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80',
        'Ph\u00f2ng \u0110\u00f4i Ti\u00eau Chu\u1ea9n': 'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=600&q=80',
        'Ph\u00f2ng 3 Ng\u01b0\u1eddi (Triple)': 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80',
        'Ph\u00f2ng Gia \u0110\u00ecnh': 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=600&q=80',
        'Ph\u00f2ng VIP Cao C\u1ea5p': 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=600&q=80'
    };
    const defaultImg = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600&q=80';

    function selectRoom(element, roomData) {
        selectedRoomId = roomData.id;
        selectedRoomData = roomData;

        // Highlight selected card
        document.querySelectorAll('.room-card').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');

        // Toggle panels
        document.getElementById('empty-detail-panel').style.display = 'none';
        document.getElementById('room-detail-panel').style.display = 'flex'; // Use flex to maintain panel styling

        // Update detail info
        document.getElementById('detail-title').innerText = 'Ph\u00f2ng ' + roomData.room_number;
        document.getElementById('detail-type').innerText = roomData.type_name;
        document.getElementById('detail-capacity').innerText = roomData.max_guests + ' ng\u01b0\u1eddi';
        document.getElementById('detail-price').innerText = new Intl.NumberFormat('vi-VN').format(roomData.price) + ' \u0111 / \u0111\u00eam';
        document.getElementById('detail-amenities').innerText = roomData.amenities_list || 'Kh\u00f4ng c\u00f3 ti\u1ec7n \u00edch \u0111\u1eb7c bi\u1ec7t';

        // Update image based on room type
        const imageUrl = roomImages[roomData.type_name] || defaultImg;
        document.getElementById('detail-img').src = imageUrl;

        // Update status badge inside detail panel
        let badgeClass = 'bg-secondary';
        let statusText = '';
        switch(roomData.status) {
            case 'available': badgeClass = 'bg-success'; statusText = '\u0110ang tr\u1ed1ng'; break;
            case 'soon_to_checkin': badgeClass = 'bg-warning text-dark'; statusText = 'S\u1eafp nh\u1eadn'; break;
            case 'occupied': badgeClass = 'bg-primary'; statusText = '\u0110ang s\u1eed d\u1ee5ng'; break;
            case 'soon_to_checkout': badgeClass = 'bg-danger'; statusText = 'S\u1eafp tr\u1ea3'; break;
        }
        document.getElementById('detail-status-badge').innerHTML = `<span class="badge ${badgeClass} rounded-pill px-3">${statusText}</span>`;

        // Enable/disable buttons based on status to guide UX
        resetActionButtons(roomData.status);
    }

    function closeDetailPanel() {
        document.getElementById('room-detail-panel').style.display = 'none';
        document.getElementById('empty-detail-panel').style.display = 'flex';
        document.querySelectorAll('.room-card').forEach(el => el.classList.remove('selected'));
        selectedRoomId = null;
        selectedRoomData = null;
    }

    function resetActionButtons(currentStatus) {
        // Reset all buttons
        const btnAvailable = document.getElementById('btn-action-available');
        const btnSoonCheckin = document.getElementById('btn-action-soon_to_checkin');
        const btnOccupied = document.getElementById('btn-action-occupied');

        btnAvailable.disabled = false;
        btnSoonCheckin.disabled = false;
        btnOccupied.disabled = false;

        // Context-aware button states to prevent checking in occupied rooms
        if (currentStatus === 'available') {
            btnAvailable.disabled = true; // Already empty
        } else if (currentStatus === 'occupied') {
            btnSoonCheckin.disabled = true; // Can't reserve when occupied
            btnOccupied.disabled = true; // Can't check-in when already occupied
        } else if (currentStatus === 'soon_to_checkin') {
            btnSoonCheckin.disabled = true; // Already reserved
        } else if (currentStatus === 'soon_to_checkout') {
            btnOccupied.disabled = true; // Already occupied
            btnSoonCheckin.disabled = true; // Already occupied
        }
    }

    // AJAX Call to update status
    function triggerStatusUpdate(newStatus) {
        if (!selectedRoomId) return;

        const url = `/?controller=reception&action=updateStatus&room_id=${selectedRoomId}&status=${newStatus}`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local counts
                updateStatusCounts(selectedRoomData.status, newStatus);

                // Update selectedRoomData status
                selectedRoomData.status = newStatus;

                // Update UI Room Card
                const card = document.getElementById(`room-card-${selectedRoomId}`);
                if (card) {
                    // Remove old status classes
                    card.classList.remove('status-available', 'status-soon_to_checkin', 'status-occupied', 'status-soon_to_checkout');
                    card.classList.add(`status-${newStatus}`);

                    // Update card status text and icon
                    const statusTextEl = card.querySelector('.room-status-text');
                    const iconEl = card.querySelector('.room-icon');
                    
                    let statusLabel = '';
                    let iconClass = 'fa-door-open';
                    
                    switch(newStatus) {
                        case 'available': statusLabel = '\u0110ang tr\u1ed1ng'; iconClass = 'fa-door-open'; break;
                        case 'soon_to_checkin': statusLabel = 'S\u1eafp nh\u1eadn'; iconClass = 'fa-calendar-day'; break;
                        case 'occupied': statusLabel = '\u0110ang s\u1eed d\u1ee5ng'; iconClass = 'fa-user-check'; break;
                        case 'soon_to_checkout': statusLabel = 'S\u1eafp tr\u1ea3'; iconClass = 'fa-bell-concierge'; break;
                    }
                    
                    if (statusTextEl) statusTextEl.innerText = statusLabel;
                    if (iconEl) {
                        iconEl.className = `fa-solid ${iconClass} room-icon`;
                    }
                    
                    // Update card attributes
                    card.setAttribute('data-status', newStatus);
                }

                // Update details view badge
                let badgeClass = 'bg-secondary';
                let statusText = '';
                switch(newStatus) {
                    case 'available': badgeClass = 'bg-success'; statusText = '\u0110ang tr\u1ed1ng'; break;
                    case 'soon_to_checkin': badgeClass = 'bg-warning text-dark'; statusText = 'S\u1eafp nh\u1eadn'; break;
                    case 'occupied': badgeClass = 'bg-primary'; statusText = '\u0110ang s\u1eed d\u1ee5ng'; break;
                    case 'soon_to_checkout': badgeClass = 'bg-danger'; statusText = 'S\u1eafp tr\u1ea3'; break;
                }
                document.getElementById('detail-status-badge').innerHTML = `<span class="badge ${badgeClass} rounded-pill px-3">${statusText}</span>`;

                // Update buttons
                resetActionButtons(newStatus);

                // Run filters to check if card should hide
                filterRooms();

                // Show Success Toast
                showToast(data.message, 'bg-success');
            } else {
                showToast('Lỗi: ' + data.message, 'bg-danger');
            }
        })
        .catch(error => {
            console.error(error);
            showToast('Lỗi hệ thống khi cập nhật trạng thái phòng.', 'bg-danger');
        });
    }

    // Helper to update legend numbers
    function updateStatusCounts(oldStatus, newStatus) {
        const countOld = document.getElementById(`count-${oldStatus}`);
        const countNew = document.getElementById(`count-${newStatus}`);
        
        if (countOld) {
            let val = parseInt(countOld.innerText);
            if (val > 0) countOld.innerText = val - 1;
        }
        if (countNew) {
            let val = parseInt(countNew.innerText);
            countNew.innerText = val + 1;
        }
    }

    // Show Custom Toast
    function showToast(message, bgClass) {
        const toastEl = document.getElementById('statusToast');
        const messageEl = document.getElementById('toastMessage');
        
        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning');
        toastEl.classList.add(bgClass);
        messageEl.innerText = message;
        
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    // Real-time Filtering Javascript
    function filterRooms() {
        const searchVal = document.getElementById('search-input').value.toLowerCase().trim();
        const floorVal = document.getElementById('filter-floor').value;
        const typeVal = document.getElementById('filter-type').value;
        const guestsVal = document.getElementById('filter-guests').value;
        const statusVal = document.getElementById('filter-status').value;
        
        document.querySelectorAll('.room-card-wrapper').forEach(wrapper => {
            const card = wrapper.querySelector('.room-card');
            const floor = card.getAttribute('data-floor');
            const type = card.getAttribute('data-type');
            const guests = parseInt(card.getAttribute('data-guests'));
            const status = card.getAttribute('data-status');
            const search = card.getAttribute('data-search');
            
            let match = true;
            
            if (searchVal && !search.includes(searchVal)) match = false;
            if (floorVal !== 'Tất cả' && floor !== floorVal) match = false;
            if (typeVal !== 'Tất cả' && type !== typeVal) match = false;
            
            if (guestsVal !== 'Tất cả') {
                if (guestsVal === '5+') {
                    if (guests < 5) match = false;
                } else {
                    if (guests !== parseInt(guestsVal)) match = false;
                }
            }
            if (statusVal !== 'Tất cả' && status !== statusVal) match = false;
            
            if (match) {
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
            }
        });
        
        // Hide floors that have zero matching rooms
        document.querySelectorAll('.floor-section').forEach(section => {
            const visibleRooms = section.querySelectorAll('.room-card-wrapper[style="display: block;"]');
            if (visibleRooms.length === 0) {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
            }
        });
    }

    // Legend Click Filter
    function toggleLegendFilter(element) {
        const statusVal = element.getAttribute('data-status-val');
        const filterStatusSelect = document.getElementById('filter-status');
        
        if (element.classList.contains('active-filter')) {
            // Unfilter
            element.classList.remove('active-filter');
            filterStatusSelect.value = 'Tất cả';
        } else {
            // Remove active classes from others
            document.querySelectorAll('.legend-badge').forEach(el => el.classList.remove('active-filter'));
            element.classList.add('active-filter');
            filterStatusSelect.value = statusVal;
        }
        
        filterRooms();
    }

    function resetFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-floor').value = 'Tất cả';
        document.getElementById('filter-type').value = 'Tất cả';
        document.getElementById('filter-guests').value = 'Tất cả';
        document.getElementById('filter-status').value = 'Tất cả';
        
        document.querySelectorAll('.legend-badge').forEach(el => el.classList.remove('active-filter'));
        
        filterRooms();
    }

    // Run filter on initial load
    window.addEventListener('DOMContentLoaded', () => {
        resetFilters();
    });
</script>