document.addEventListener('DOMContentLoaded', function () {
    function createGreetingArticle(text) {
        var article = document.createElement('article');
        article.className = 'chatbot-msg bot';

        var bubble = document.createElement('div');
        bubble.className = 'chatbot-bubble';
        bubble.textContent = text;
        article.appendChild(bubble);

        return article;
    }

    function initChatbotShell(shell) {
        var form = shell.querySelector('.chatbot-form');
        var input = shell.querySelector('textarea[name="message"]');
        var messages = shell.querySelector('.chatbot-messages');
        var confirmEndpoint = shell.dataset.confirmEndpoint || '';
        var briefEndpoint = shell.dataset.briefEndpoint || '';
        var resetEndpoint = shell.dataset.resetEndpoint || '';
        var csrfInput = form ? form.querySelector('input[name="_csrf_token"]') : null;
        var csrfToken = csrfInput ? csrfInput.value : '';
        var briefLoaded = false;
        var briefShown = false;

        if (!form || !input || !messages) {
            return null;
        }

        function setBadge(count) {
            var badge = document.getElementById('chatbotBriefBadge');
            if (!badge) {
                return;
            }

            if (count > 0) {
                badge.hidden = false;
                badge.textContent = String(count > 9 ? '9+' : count);
            } else {
                badge.hidden = true;
                badge.textContent = '0';
            }
        }

        function appendMessage(role, text, actions, suggestions, actionDraft) {
            var article = document.createElement('article');
            article.className = 'chatbot-msg ' + (role === 'user' ? 'user' : 'bot');

            var bubble = document.createElement('div');
            bubble.className = 'chatbot-bubble';
            bubble.textContent = text;
            article.appendChild(bubble);

            if (role !== 'user') {
                var meta = document.createElement('div');
                meta.className = 'chatbot-meta';

                var copyButton = document.createElement('button');
                copyButton.type = 'button';
                copyButton.className = 'chatbot-copy-btn';
                copyButton.textContent = 'Sao chép';
                copyButton.setAttribute('data-copy-text', text || '');
                meta.appendChild(copyButton);

                article.appendChild(meta);
            }

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

        function appendTypingIndicator() {
            removeTypingIndicator();

            var article = document.createElement('article');
            article.className = 'chatbot-msg bot chatbot-typing-row';
            article.setAttribute('data-chatbot-typing', '1');

            var bubble = document.createElement('div');
            bubble.className = 'chatbot-bubble chatbot-typing-bubble';
            bubble.innerHTML = '<span></span><span></span><span></span>';
            article.appendChild(bubble);

            messages.appendChild(article);
            messages.scrollTop = messages.scrollHeight;
        }

        function removeTypingIndicator() {
            var existing = messages.querySelector('[data-chatbot-typing="1"]');
            if (existing) {
                existing.remove();
            }
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

        async function parseJsonResponse(response) {
            var rawText = await response.text();
            try {
                return rawText ? JSON.parse(rawText) : {};
            } catch (err) {
                return {
                    ok: false,
                    message: rawText || 'INVALID_JSON_RESPONSE'
                };
            }
        }

        async function fetchBriefData() {
            if (!briefEndpoint) {
                return [];
            }

            var response = await fetch(briefEndpoint, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            var data = await parseJsonResponse(response);
            if (!response.ok || !data.ok) {
                return [];
            }

            var items = Array.isArray(data.items) ? data.items.filter(function (item) {
                return typeof item === 'string' && item.trim() !== '';
            }) : [];
            setBadge(items.length);
            briefLoaded = true;
            return items;
        }

        async function ensureBriefShown(forceShow) {
            try {
                var items = await fetchBriefData();
                if ((forceShow || !briefShown) && items.length > 0) {
                    appendMessage('bot', 'Tóm tắt nhanh hôm nay của bạn:', items, [], null);
                    briefShown = true;
                }
            } catch (err) {
                setBadge(0);
            }
        }

        async function sendMessage(text) {
            appendMessage('user', text);
            setLoading(true);
            appendTypingIndicator();

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

                var data = await parseJsonResponse(response);
                removeTypingIndicator();

                if (!response.ok || !data.ok) {
                    appendMessage('bot', 'Yêu cầu thất bại. ' + (data.message || 'Vui lòng thử lại.'));
                    return;
                }

                appendMessage('bot', data.reply || 'Không có nội dung trả lời.', data.actions || [], data.suggestions || [], data.action_draft || null);
                if (briefLoaded) {
                    ensureBriefShown(false);
                }
            } catch (err) {
                removeTypingIndicator();
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

                var data = await parseJsonResponse(response);
                appendMessage('bot', data.reply || 'Không có phản hồi xác nhận.', data.actions || [], data.suggestions || []);
                trigger.textContent = response.ok && data.ok ? 'Đã thực thi' : 'Thực thi thất bại';
            } catch (err) {
                appendMessage('bot', 'Không thể xác nhận hành động lúc này. Vui lòng thử lại sau.');
                trigger.disabled = false;
                trigger.textContent = 'Xác nhận thực thi';
            }
        }

        async function resetConversation() {
            if (!resetEndpoint) {
                return;
            }

            try {
                var formData = new FormData();
                if (csrfToken) {
                    formData.set('_csrf_token', csrfToken);
                }

                var response = await fetch(resetEndpoint, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                var data = await parseJsonResponse(response);
                if (!response.ok || !data.ok) {
                    appendMessage('bot', 'Không thể làm mới cuộc trò chuyện lúc này.');
                    return;
                }

                messages.innerHTML = '';
                messages.appendChild(createGreetingArticle('Xin chào. Tôi đã sẵn sàng cho một cuộc trò chuyện mới.'));
                briefShown = false;
                await ensureBriefShown(true);
            } catch (err) {
                appendMessage('bot', 'Không thể làm mới cuộc trò chuyện lúc này.');
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

            if (target.classList.contains('chatbot-copy-btn')) {
                var copyText = target.getAttribute('data-copy-text') || '';
                if (!copyText) {
                    return;
                }
                navigator.clipboard.writeText(copyText).then(function () {
                    target.textContent = 'Đã sao chép';
                    window.setTimeout(function () {
                        target.textContent = 'Sao chép';
                    }, 1200);
                }).catch(function () {
                    target.textContent = 'Không sao chép được';
                });
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

        shell._chatbotApi = {
            ensureBriefShown: ensureBriefShown,
            resetConversation: resetConversation,
            refreshBadge: function () {
                fetchBriefData().catch(function () {
                    setBadge(0);
                });
            }
        };

        return shell._chatbotApi;
    }

    var shellApis = [];
    document.querySelectorAll('[data-chatbot-shell]').forEach(function (shell) {
        var api = initChatbotShell(shell);
        if (api) {
            shellApis.push({ element: shell, api: api });
        }
    });

    var widget = document.getElementById('chatbotFloat');
    var launcher = document.getElementById('chatbotLauncher');
    var panel = document.getElementById('chatbotWidgetPanel');
    var closeBtn = document.getElementById('chatbotWidgetClose');

    if (shellApis.length > 0) {
        shellApis[0].api.refreshBadge();
    }

    if (widget && launcher && panel) {
        var widgetShell = panel.querySelector('[data-chatbot-shell]');
        var widgetApi = null;
        shellApis.forEach(function (entry) {
            if (entry.element === widgetShell) {
                widgetApi = entry.api;
            }
        });

        function setWidgetOpen(isOpen) {
            widget.classList.toggle('open', isOpen);
            launcher.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

            if (isOpen && widgetApi) {
                widgetApi.ensureBriefShown(true);
            }
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

    document.addEventListener('click', function (e) {
        var target = e.target;
        if (!(target instanceof HTMLElement) || !target.hasAttribute('data-chatbot-reset')) {
            return;
        }

        var shell = target.closest('[data-chatbot-shell]');
        if (!shell) {
            var panel = target.closest('.chatbot-widget-panel');
            shell = panel ? panel.querySelector('[data-chatbot-shell]') : null;
        }

        if (shell && shell._chatbotApi && typeof shell._chatbotApi.resetConversation === 'function') {
            shell._chatbotApi.resetConversation();
        }
    });
});