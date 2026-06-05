/**
 * date-validate.js
 * ─────────────────────────────────────────────────────
 * Module tái sử dụng: ràng buộc ngày nhận phòng < ngày trả phòng
 *
 * CÁCH SỬ DỤNG:
 *   <script src="<?= BASE_URL ?>/js/date-validate.js"></script>
 *   <script>
 *     HotelDateValidator.init({
 *       checkInId:  'checkIn',   // id của input check_in
 *       checkOutId: 'checkOut',  // id của input check_out
 *       formId:     'myForm',    // (tuỳ chọn) id của form để validate khi submit
 *     });
 *   </script>
 *
 * Input chỉ cần có id tương ứng, không cần class hay data- đặc biệt.
 * Lỗi sẽ hiển thị bên dưới input qua một <div class="hotel-date-error">.
 * ─────────────────────────────────────────────────────
 */

const HotelDateValidator = (() => {

    const TODAY = new Date().toISOString().split('T')[0];

    /* ── Tạo/lấy div hiển thị lỗi bên dưới input ── */
    function getOrCreateError(input) {
        let el = input.parentElement.querySelector('.hotel-date-error');
        if (!el) {
            el = document.createElement('div');
            el.className = 'hotel-date-error';
            el.style.cssText = [
                'font-size:.78rem',
                'color:#C0392B',
                'margin-top:4px',
                'min-height:18px',
                'display:flex',
                'align-items:center',
                'gap:4px',
            ].join(';');
            input.parentElement.appendChild(el);
        }
        return el;
    }

    function showErr(input, msg) {
        input.style.borderColor = '#C0392B';
        input.style.boxShadow   = '0 0 0 3px rgba(192,57,43,.12)';
        const el = getOrCreateError(input);
        el.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${msg}`;
    }

    function clearErr(input) {
        input.style.borderColor = '';
        input.style.boxShadow   = '';
        const el = getOrCreateError(input);
        el.textContent = '';
    }

    /* ── Cập nhật min của checkOut mỗi khi checkIn thay đổi ── */
    function syncOutMin(inEl, outEl) {
        if (!inEl.value) return;

        const d = new Date(inEl.value);
        d.setDate(d.getDate() + 1);
        const minOut = d.toISOString().split('T')[0];
        outEl.min = minOut;

        if (outEl.value && outEl.value <= inEl.value) {
            outEl.value = '';
            showErr(outEl, 'Ngày trả phòng phải sau ngày nhận phòng.');
        } else if (outEl.value) {
            clearErr(outEl);
        }
    }

    /* ── Validate toàn bộ cặp ngày, trả về true nếu hợp lệ ── */
    function validate(inEl, outEl) {
        let ok = true;

        if (!inEl.value) {
            showErr(inEl, 'Vui lòng chọn ngày nhận phòng.');
            ok = false;
        } else if (inEl.value < TODAY) {
            showErr(inEl, 'Ngày nhận phòng không được là ngày trong quá khứ.');
            ok = false;
        } else {
            clearErr(inEl);
        }

        if (!outEl.value) {
            showErr(outEl, 'Vui lòng chọn ngày trả phòng.');
            ok = false;
        } else if (inEl.value && outEl.value <= inEl.value) {
            showErr(outEl, 'Ngày trả phòng phải sau ngày nhận phòng.');
            ok = false;
        } else {
            clearErr(outEl);
        }

        return ok;
    }

    /* ── Khởi tạo ── */
    function init({ checkInId, checkOutId, formId }) {
        const inEl  = document.getElementById(checkInId);
        const outEl = document.getElementById(checkOutId);

        if (!inEl || !outEl) {
            console.warn('[HotelDateValidator] Không tìm thấy input:', checkInId, checkOutId);
            return;
        }

        /* Thiết lập min mặc định */
        inEl.min  = inEl.min  || TODAY;
        const tomorrow = (() => {
            const d = new Date(TODAY); d.setDate(d.getDate() + 1);
            return d.toISOString().split('T')[0];
        })();
        outEl.min = outEl.min || tomorrow;

        /* Đồng bộ ngay nếu đã có giá trị từ server */
        if (inEl.value) syncOutMin(inEl, outEl);

        /* Sự kiện */
        inEl.addEventListener('change', () => {
            if (inEl.value < TODAY) {
                showErr(inEl, 'Ngày nhận phòng không được là ngày trong quá khứ.');
            } else {
                clearErr(inEl);
                syncOutMin(inEl, outEl);
            }
        });

        outEl.addEventListener('change', () => {
            if (!inEl.value) {
                outEl.value = '';
                showErr(outEl, 'Vui lòng chọn ngày nhận phòng trước.');
                return;
            }
            if (outEl.value <= inEl.value) {
                outEl.value = '';
                showErr(outEl, 'Ngày trả phòng phải sau ngày nhận phòng.');
            } else {
                clearErr(outEl);
            }
        });

        /* Validate khi submit form (tuỳ chọn) */
        if (formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (!validate(inEl, outEl)) {
                        e.preventDefault();
                        e.stopPropagation();
                        inEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        }
    }

    return { init, validate };
})();