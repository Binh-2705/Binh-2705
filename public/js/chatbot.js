document.addEventListener('DOMContentLoaded', function () {
    function initChatbotShell(shell) {
        var form = shell.querySelector('.chatbot-form');
        var input = shell.querySelector('textarea[name="message"]');
        var messages = shell.querySelector('.chatbot-messages');
        var confirmEndpoint = shell.dataset.confirmEndpoint || '';
        var csrfInput = form ? form.querySelector('input[name="_csrf_token"]') : null;
        var csrfToken = csrfInput ? csrfInput.value : '';

        if (!form || !input || !messages) {
            return;
        }

        function appendMessage(role, text, actions, suggestions, actionDraft) {
            var article = document.createElement('article');
            article.className = 'chatbot-msg ' + (role === 'user' ? 'user' : 'bot');

            var bubble = document.createElement('div');
            bubble.className = 'chatbot-bubble';
            bubble.textContent = text;
            article.appendChild(bubble);

            if (Array.isArray(actions) && actions.length > 0) {
                var list = document.createElement('ul');
                list.className = 'chatbot-action-list';

                actions.forEach(function (item) {
                    if (typeof item !== 'string' || item.trim() === '') {
                        return;
                    }
                    var li = document.createElement('li');
                    li.textContent = item;
                    list.appendChild(li);
                });

                if (list.children.length > 0) {
                    article.appendChild(list);
                }
            }

            if (role !== 'user' && Array.isArray(suggestions) && suggestions.length > 0) {
                var suggestionWrap = document.createElement('div');
                suggestionWrap.className = 'chatbot-inline-suggestions';

                suggestions.forEach(function (item) {
                    if (typeof item !== 'string' || item.trim() === '') {
                        return;
                    }

                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'chatbot-chip chatbot-inline-chip';
                    button.textContent = item;
                    button.setAttribute('data-prompt', item);
                    suggestionWrap.appendChild(button);
                });

                if (suggestionWrap.children.length > 0) {
                    article.appendChild(suggestionWrap);
                }
            }

            if (role !== 'user' && actionDraft && actionDraft.token) {
                var draftCard = document.createElement('div');
                draftCard.className = 'chatbot-draft-card';

                var draftActionType = actionDraft.action_type || '';
                var needReason = draftActionType === 'leave_approve' || draftActionType === 'leave_reject';

                var title = document.createElement('strong');
                title.textContent = actionDraft.title || 'Xác nhận hành động';
                draftCard.appendChild(title);

                if (actionDraft.summary) {
                    var summary = document.createElement('p');
                    summary.textContent = actionDraft.summary;
                    draftCard.appendChild(summary);
                }

                if (needReason) {
                    var reasonLabel = document.createElement('label');
                    reasonLabel.className = 'chatbot-reason-label';
                    reasonLabel.textContent = 'Lý do xác nhận (bắt buộc)';
                    draftCard.appendChild(reasonLabel);

                    var reasonInput = document.createElement('textarea');
                    reasonInput.className = 'chatbot-reason-input';
                    reasonInput.setAttribute('rows', '2');
                    reasonInput.setAttribute('maxlength', '300');
                    reasonInput.setAttribute('placeholder', 'Nhập lý do duyệt/từ chối...');
                    reasonInput.setAttribute('data-reason-for', actionDraft.token);
                    draftCard.appendChild(reasonInput);
                }

                var hint = document.createElement('p');
                hint.className = 'chatbot-draft-hint';
                hint.textContent = 'Lưu ý: yêu cầu xác nhận sẽ hết hạn sau 10 phút.';
                draftCard.appendChild(hint);

                var confirmBtn = document.createElement('button');
                confirmBtn.type = 'button';
                confirmBtn.className = 'btn search chatbot-confirm-btn';
                confirmBtn.textContent = actionDraft.confirm_label || 'Xác nhận thực thi';
                confirmBtn.setAttribute('data-action-token', actionDraft.token);
                if (needReason) {
                    confirmBtn.setAttribute('data-require-reason', '1');
                }
                draftCard.appendChild(confirmBtn);

                article.appendChild(draftCard);
            }

            messages.appendChild(article);
            messages.scrollTop = messages.scrollHeight;
        }

        function setLoading(isLoading) {
            form.classList.toggle('is-loading', isLoading);
            input.disabled = isLoading;
            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = isLoading;
                submitBtn.textContent = isLoading ? 'Đang gửi...' : 'Gửi';
            }
        }

        async function sendMessage(text) {
            appendMessage('user', text);
            setLoading(true);

            try {
                var formData = new FormData(form);
                formData.set('message', text);

                var response = await fetch(form.action || shell.dataset.endpoint, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                var data = await response.json();
                if (!response.ok || !data.ok) {
                    appendMessage('bot', 'Yêu cầu thất bại. Vui lòng thử lại.');
                    return;
                }

                appendMessage('bot', data.reply || 'Không có nội dung trả lời.', data.actions || [], data.suggestions || [], data.action_draft || null);
            } catch (err) {
                appendMessage('bot', 'Không thể kết nối bot service. Kiểm tra service Python và thử lại.');
            } finally {
                setLoading(false);
                input.focus();
            }
        }

        async function confirmDraft(token, trigger) {
            if (!confirmEndpoint || !token) {
                return;
            }

            var reason = '';
            if (trigger.getAttribute('data-require-reason') === '1') {
                var reasonInput = shell.querySelector('.chatbot-reason-input[data-reason-for="' + token + '"]');
                reason = reasonInput ? reasonInput.value.trim() : '';
                if (!reason) {
                    appendMessage('bot', 'Vui lòng nhập lý do xác nhận trước khi thực thi hành động.');
                    if (reasonInput) {
                        reasonInput.focus();
                    }
                    return;
                }
            }

            trigger.disabled = true;
            trigger.textContent = 'Đang xác nhận...';

            try {
                var formData = new FormData();
                formData.set('action_token', token);
                if (reason) {
                    formData.set('confirm_reason', reason);
                }
                if (csrfToken) {
                    formData.set('_csrf_token', csrfToken);
                }

                var response = await fetch(confirmEndpoint, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                var data = await response.json();
                appendMessage('bot', data.reply || 'Không có phản hồi xác nhận.', data.actions || [], data.suggestions || []);
                trigger.textContent = response.ok && data.ok ? 'Đã thực thi' : 'Thực thi thất bại';
            } catch (err) {
                appendMessage('bot', 'Không thể xác nhận hành động lúc này. Vui lòng thử lại sau.');
                trigger.disabled = false;
                trigger.textContent = 'Xác nhận thực thi';
            }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = input.value.trim();
            if (!text) {
                return;
            }
            input.value = '';
            sendMessage(text);
        });

        shell.addEventListener('click', function (e) {
            var target = e.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }
            if (!target.classList.contains('chatbot-chip')) {
                if (!target.classList.contains('chatbot-confirm-btn')) {
                    return;
                }

                var actionToken = target.getAttribute('data-action-token') || '';
                if (!actionToken) {
                    return;
                }
                confirmDraft(actionToken, target);
                return;
            }
            var prompt = target.getAttribute('data-prompt') || '';
            if (!prompt.trim()) {
                return;
            }
            input.value = '';
            sendMessage(prompt.trim());
        });
    }

    document.querySelectorAll('[data-chatbot-shell]').forEach(initChatbotShell);

    var widget = document.getElementById('chatbotFloat');
    var launcher = document.getElementById('chatbotLauncher');
    var panel = document.getElementById('chatbotWidgetPanel');
    var closeBtn = document.getElementById('chatbotWidgetClose');

    if (widget && launcher && panel) {
        function setWidgetOpen(isOpen) {
            widget.classList.toggle('open', isOpen);
            launcher.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        launcher.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            setWidgetOpen(!widget.classList.contains('open'));
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                setWidgetOpen(false);
            });
        }

        document.addEventListener('click', function (e) {
            if (!widget.contains(e.target)) {
                setWidgetOpen(false);
            }
        });
    }
});
