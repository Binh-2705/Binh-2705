/**
 * Form Validation & Enhancement
 */
(function () {
    'use strict';

    // ===== VALIDATE EMAIL =====
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // ===== VALIDATE PHONE =====
    function validatePhone(phone) {
        const re = /^[0-9]{9,11}$/;
        return re.test(phone.replace(/\s|-/g, ''));
    }

    // ===== ADD VALIDATION ATTRIBUTES =====
    function enhanceForm() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Input: Họ tên
            const nameInputs = form.querySelectorAll('input[name*="hoten"], input[name*="HoTen"], input[name*="ten"]');
            nameInputs.forEach(el => {
                if (!el.hasAttribute('minlength')) el.setAttribute('minlength', '2');
                if (!el.hasAttribute('maxlength')) el.setAttribute('maxlength', '120');
            });

            // Input: Điện thoại
            const phoneInputs = form.querySelectorAll('input[name*="dienthoai"], input[name*="DienThoai"], input[name*="phone"]');
            phoneInputs.forEach(el => {
                el.setAttribute('type', 'tel');
                el.setAttribute('pattern', '[0-9]{9,11}');
                el.setAttribute('placeholder', 'VD: 0901234567');
            });

            // Input: Email
            const emailInputs = form.querySelectorAll('input[type="email"]');
            emailInputs.forEach(el => {
                if (!el.hasAttribute('maxlength')) el.setAttribute('maxlength', '150');
            });

            // Input: Ngày sinh
            const birthdateInputs = form.querySelectorAll('input[name*="ngaysinh"], input[name*="NgaySinh"]');
            birthdateInputs.forEach(el => {
                const today = new Date().toISOString().split('T')[0];
                const minDate = new Date();
                minDate.setFullYear(minDate.getFullYear() - 100);
                el.setAttribute('type', 'date');
                el.setAttribute('max', today);
                el.setAttribute('min', minDate.toISOString().split('T')[0]);
            });

            // Input: Ngày ký, Ngày bắt đầu
            const dateInputs = form.querySelectorAll('input[name*="ngay"], input[name*="Ngay"]');
            dateInputs.forEach(el => {
                if (!el.hasAttribute('type') || el.getAttribute('type') !== 'date') {
                    el.setAttribute('type', 'date');
                }
            });

            // Textarea: Ghi chú, Mô tả
            const textareas = form.querySelectorAll('textarea');
            textareas.forEach(el => {
                if (!el.hasAttribute('maxlength')) el.setAttribute('maxlength', '2000');
                
                // Add character counter
                const maxLength = el.getAttribute('maxlength');
                if (maxLength) {
                    const charCount = document.createElement('div');
                    charCount.className = 'char-count';
                    charCount.style.fontSize = '12px';
                    charCount.style.color = '#6b7280';
                    charCount.style.marginTop = '3px';
                    charCount.style.textAlign = 'right';
                    charCount.textContent = `0 / ${maxLength}`;
                    el.parentElement.appendChild(charCount);
                    
                    // Update counter on input
                    el.addEventListener('input', function () {
                        charCount.textContent = `${this.value.length} / ${maxLength}`;
                        if (this.value.length >= maxLength * 0.9) {
                            charCount.style.color = '#f59e0b';
                        } else if (this.value.length >= maxLength) {
                            charCount.style.color = '#dc3545';
                        } else {
                            charCount.style.color = '#6b7280';
                        }
                    });
                }
            });

            // Input: Số lượng, Bậc, v.v.
            const numberInputs = form.querySelectorAll('input[name*="soluong"], input[name*="SoLuong"], input[name*="luong"], input[name*="Luong"], input[name*="diem"], input[name*="Diem"]');
            numberInputs.forEach(el => {
                el.setAttribute('type', 'number');
                el.setAttribute('min', '0');
            });

            // Input: Số hợp đồng, Số BH
            const contractInputs = form.querySelectorAll('input[name*="sohopdong"], input[name*="SoHopDong"], input[name*="soBHXH"], input[name*="SoBHXH"]');
            contractInputs.forEach(el => {
                if (!el.hasAttribute('minlength')) el.setAttribute('minlength', '2');
                if (!el.hasAttribute('maxlength')) el.setAttribute('maxlength', '50');
            });
        });
    }

    // ===== FORM SUBMIT VALIDATION =====
    function attachValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                let isValid = true;
                const formGroups = form.querySelectorAll('.form-group');

                formGroups.forEach(group => {
                    const input = group.querySelector('input, select, textarea');
                    if (!input) return;

                    // Remove previous error
                    group.classList.remove('error');
                    const errorText = group.querySelector('.error-text');
                    if (errorText) errorText.style.display = 'none';

                    // Check required
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        isValid = false;
                        group.classList.add('error');
                        showError(group, 'Trường này không được để trống.');
                        return;
                    }

                    // Check email
                    if (input.type === 'email' && input.value && !validateEmail(input.value)) {
                        isValid = false;
                        group.classList.add('error');
                        showError(group, 'Email không hợp lệ.');
                        return;
                    }

                    // Check phone
                    if (input.type === 'tel' && input.value && !validatePhone(input.value)) {
                        isValid = false;
                        group.classList.add('error');
                        showError(group, 'Điện thoại phải từ 9-11 chữ số.');
                        return;
                    }

                    // Check minlength
                    if (input.hasAttribute('minlength')) {
                        const min = parseInt(input.getAttribute('minlength'));
                        if (input.value.length < min) {
                            isValid = false;
                            group.classList.add('error');
                            showError(group, `Tối thiểu ${min} ký tự.`);
                            return;
                        }
                    }

                    // Check maxlength
                    if (input.hasAttribute('maxlength')) {
                        const max = parseInt(input.getAttribute('maxlength'));
                        if (input.value.length > max) {
                            isValid = false;
                            group.classList.add('error');
                            showError(group, `Tối đa ${max} ký tự.`);
                            return;
                        }
                    }

                    // Check date logic (for ngày bắt đầu và ngày kết thúc)
                    if (input.name && (input.name.includes('BatDau') || input.name.includes('KetThuc'))) {
                        const allDates = form.querySelectorAll('input[name*="BatDau"], input[name*="KetThuc"]');
                        if (allDates.length === 2) {
                            const [startInput, endInput] = allDates;
                            if (startInput.value && endInput.value && endInput.value < startInput.value) {
                                if (input === endInput) {
                                    isValid = false;
                                    group.classList.add('error');
                                    showError(group, 'Ngày kết thúc phải lớn hơn ngày bắt đầu.');
                                }
                            }
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                // Disable submit button while form is being submitted
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                }
            });
        });
    }

    // ===== SHOW ERROR MESSAGE =====
    function showError(group, message) {
        let errorText = group.querySelector('.error-text');
        if (!errorText) {
            errorText = document.createElement('div');
            errorText.className = 'error-text';
            group.appendChild(errorText);
        }
        errorText.textContent = message;
        errorText.style.display = 'block';
    }

    // ===== CLEAR ERROR ON INPUT CHANGE =====
    function attachInputListeners() {
        const inputs = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
        inputs.forEach(input => {
            input.addEventListener('change', function () {
                const group = this.closest('.form-group');
                if (group) {
                    group.classList.remove('error');
                    const errorText = group.querySelector('.error-text');
                    if (errorText) errorText.style.display = 'none';
                }
            });

            // Real-time validation for specific fields
            if (input.type === 'email' && input.hasAttribute('required')) {
                input.addEventListener('blur', function () {
                    if (this.value && !validateEmail(this.value)) {
                        const group = this.closest('.form-group');
                        group.classList.add('error');
                        showError(group, 'Email không hợp lệ.');
                    }
                });
            }
        });
    }

    // ===== INITIALIZE =====
    document.addEventListener('DOMContentLoaded', function () {
        enhanceForm();
        attachValidation();
        attachInputListeners();
    });
})();
