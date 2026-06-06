<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lễ tân - Hotel Sunshine</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Inter', sans-serif; 
            overflow: hidden; 
            margin: 0;
            padding: 0;
        }
        
        /* Top Navigation Bar */
        .navbar-top {
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
            z-index: 100;
        }
        .navbar-brand-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-brand-text {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1e293b;
            letter-spacing: -0.025em;
        }
        .navbar-menu-links {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            height: 100%;
            align-items: center;
            gap: 5px;
        }
        .navbar-menu-links li {
            height: 100%;
            display: flex;
            align-items: center;
        }
        .navbar-menu-links li a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.925rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .navbar-menu-links li a:hover {
            color: #0d6efd;
            background: #f1f5f9;
        }
        .navbar-menu-links li.active a {
            color: #0d6efd;
            background: #e0f2fe;
            font-weight: 600;
        }
        
        /* Dashboard Container */
        .dashboard-container { 
            display: flex; 
            height: calc(100vh - 70px); 
            position: relative;
        }
        
        /* Main & Panel layout */
        .main-content { 
            flex: 1; 
            overflow-y: auto; 
            padding: 24px 30px; 
            background: #f8fafc;
        }
        .right-panel { 
            width: 380px; 
            background: white; 
            border-left: 1px solid #e2e8f0; 
            padding: 24px; 
            overflow-y: auto;
            box-shadow: -2px 0 10px rgba(0,0,0,0.01);
            display: flex;
            flex-direction: column;
        }
        
        /* Scrollbar styles */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar-top">
        <div class="navbar-brand-group">
            <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px;">
                <i class="fa-solid fa-hotel fs-5"></i>
            </div>
            <span class="navbar-brand-text">Hotel Sunshine</span>
        </div>
        
        <ul class="navbar-menu-links">
            <li><a href="#"><i class="fa-solid fa-chart-line"></i> Tổng quan</a></li>
            <li class="active"><a href="#"><i class="fa-solid fa-user-tie"></i> Lễ tân</a></li>
            <li><a href="/?controller=booking&action=myBookings"><i class="fa-solid fa-calendar-check"></i> Đặt phòng</a></li>
            <li><a href="#"><i class="fa-solid fa-bed"></i> Khách lưu trú</a></li>
            <li><a href="#"><i class="fa-solid fa-users"></i> Khách hàng</a></li>
            <li><a href="#"><i class="fa-solid fa-credit-card"></i> Thanh toán</a></li>
            <li><a href="#"><i class="fa-solid fa-chart-bar"></i> Báo cáo</a></li>
        </ul>
        
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name=Reception&background=0D8ABC&color=fff" class="rounded-circle border" width="38" height="38" alt="Avatar">
                <div class="d-none d-md-block text-start" style="line-height: 1.2;">
                    <div class="fw-bold" style="font-size:0.85rem"><?= htmlspecialchars($_SESSION['user']['fullname'] ?? 'Lễ tân') ?></div>
                    <span class="text-muted" style="font-size:0.75rem">Lễ tân trưởng</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Main Content & Right Panel will be injected here -->
        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>