<?php $pageTitle = 'Đánh Giá Trải Nghiệm – ' . htmlspecialchars($roomType['type_name']); ?>

<style>
.review-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #E8E4D8;
    box-shadow: 0 10px 30px rgba(26,26,46,0.05);
    padding: 2.5rem;
    max-width: 600px;
    margin: 2rem auto;
}
.review-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: #1A1A2E;
    text-align: center;
    margin-bottom: 0.5rem;
}
.review-subtitle {
    color: #7A7A8A;
    text-align: center;
    font-size: 0.95rem;
    margin-bottom: 2rem;
}
.room-type-badge {
    background: #FAF8F3;
    border: 1px solid #E8E4D8;
    border-radius: 8px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 2rem;
}
.room-type-badge i {
    color: #C9A84C;
    font-size: 1.5rem;
}
.room-type-badge .name {
    font-weight: 600;
    color: #1A1A2E;
}
.room-type-badge .price {
    font-size: 0.85rem;
    color: #7A7A8A;
}

/* Star rating widget */
.rating-stars-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 1.5rem;
}
.star-btn {
    background: none;
    border: none;
    font-size: 2.5rem;
    color: #E8E4D8;
    cursor: pointer;
    transition: color 0.15s, transform 0.15s;
    padding: 0;
}
.star-btn:hover {
    transform: scale(1.15);
}
.star-btn.selected,
.star-btn.active {
    color: #C9A84C;
}

.rating-text {
    text-align: center;
    font-size: 0.95rem;
    font-weight: 600;
    color: #C9A84C;
    min-height: 24px;
    margin-bottom: 2rem;
}

.comment-label {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #7A7A8A;
    margin-bottom: 8px;
    display: block;
}
.comment-input {
    width: 100%;
    border: 1.5px solid #E8E4D8;
    border-radius: 12px;
    padding: 12px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    color: #1A1A2E;
    background: #FAF8F3;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
    resize: none;
    min-height: 120px;
}
.comment-input:focus {
    outline: none;
    border-color: #C9A84C;
    box-shadow: 0 0 0 4px rgba(201,168,76,0.12);
    background: #ffffff;
}

.btn-submit-review {
    width: 100%;
    background: linear-gradient(135deg, #9A7335, #C9A84C);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 1rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.05em;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    margin-top: 1.5rem;
}
.btn-submit-review:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}
.btn-submit-review:active {
    transform: translateY(0);
}
</style>

<div class="container py-4">
    <div class="review-card">
        <h1 class="review-title">Đánh Giá Trải Nghiệm</h1>
        <p class="review-subtitle">Ý kiến của quý khách sẽ giúp chúng tôi hoàn thiện chất lượng dịch vụ tốt hơn mỗi ngày.</p>

        <div class="room-type-badge">
            <i class="bi bi-door-open-fill"></i>
            <div>
                <div class="name"><?= htmlspecialchars($roomType['type_name']) ?></div>
                <div class="price">Mã đặt phòng: #<?= $booking['id'] ?> &bull; Thời gian lưu trú: <?= $booking['check_in'] ?> đến <?= $booking['check_out'] ?></div>
            </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/?controller=review&action=store" id="reviewForm" onsubmit="return validateForm()">
            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
            <input type="hidden" name="room_type_id" value="<?= $roomType['id'] ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="rating" id="ratingInput" value="0">

            <label class="comment-label text-center d-block mb-3">Quý khách đánh giá loại phòng này thế nào?</label>
            
            <div class="rating-stars-container" id="starsContainer">
                <button type="button" class="star-btn" data-value="1" onclick="setRating(1)"><i class="bi bi-star"></i></button>
                <button type="button" class="star-btn" data-value="2" onclick="setRating(2)"><i class="bi bi-star"></i></button>
                <button type="button" class="star-btn" data-value="3" onclick="setRating(3)"><i class="bi bi-star"></i></button>
                <button type="button" class="star-btn" data-value="4" onclick="setRating(4)"><i class="bi bi-star"></i></button>
                <button type="button" class="star-btn" data-value="5" onclick="setRating(5)"><i class="bi bi-star"></i></button>
            </div>
            
            <div class="rating-text" id="ratingText">Vui lòng chọn số sao</div>

            <div class="mb-3">
                <label for="commentInput" class="comment-label">Lời bình luận hoặc ý kiến đóng góp</label>
                <textarea class="comment-input" id="commentInput" name="comment" placeholder="Quý khách thích gì về loại phòng này? Hay có điểm gì chưa thực sự hài lòng..."></textarea>
            </div>

            <button type="submit" class="btn-submit-review">
                <i class="bi bi-send-fill me-2"></i>Gửi Đánh Giá Của Quý Khách
            </button>
        </form>
    </div>
</div>

<script>
const ratingTexts = {
    1: "Tệ (1/5)",
    2: "Không tốt (2/5)",
    3: "Bình thường (3/5)",
    4: "Rất tốt (4/5)",
    5: "Tuyệt vời (5/5)"
};

let currentRating = 0;

function setRating(rating) {
    currentRating = rating;
    document.getElementById('ratingInput').value = rating;
    document.getElementById('ratingText').textContent = ratingTexts[rating];
    
    const buttons = document.querySelectorAll('.star-btn');
    buttons.forEach((btn, idx) => {
        const icon = btn.querySelector('i');
        if (idx < rating) {
            btn.classList.add('selected');
            icon.className = 'bi bi-star-fill';
        } else {
            btn.classList.remove('selected');
            icon.className = 'bi bi-star';
        }
    });
}

// Hiệu ứng di chuột (hover)
const starButtons = document.querySelectorAll('.star-btn');
starButtons.forEach(btn => {
    btn.addEventListener('mouseenter', () => {
        const hoverVal = parseInt(btn.dataset.value);
        starButtons.forEach((b, idx) => {
            const icon = b.querySelector('i');
            if (idx < hoverVal) {
                b.classList.add('active');
                if (!b.classList.contains('selected')) {
                    icon.className = 'bi bi-star-fill';
                }
            } else {
                b.classList.remove('active');
                if (!b.classList.contains('selected')) {
                    icon.className = 'bi bi-star';
                }
            }
        });
    });
});

document.getElementById('starsContainer').addEventListener('mouseleave', () => {
    starButtons.forEach((b, idx) => {
        b.classList.remove('active');
        const icon = b.querySelector('i');
        if (idx < currentRating) {
            icon.className = 'bi bi-star-fill';
        } else {
            icon.className = 'bi bi-star';
        }
    });
});

function validateForm() {
    if (currentRating === 0) {
        alert("Vui lòng chọn số sao đánh giá!");
        return false;
    }
    return true;
}
</script>
