{{--
    Fenetre "Enregistrer un appel", partagee par toutes les pages du call center.
    Ouverture : n'importe quel bouton portant data-cc-log='{"cible_type":"users","cible_id":12,"alert_id":34,"nom":"..."}'.
--}}
@php
    use App\Support\CallCenterOutcomes;
@endphp

<style>
    .cc-log-dialog {
        background: var(--cc-surface);
        border: 0;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
        color: var(--cc-label);
        max-height: calc(100vh - 32px);
        max-width: 560px;
        padding: 0;
        width: calc(100vw - 32px);
    }
    .cc-log-dialog::backdrop { background: rgba(0, 0, 0, .35); }
    .cc-log-dialog form { display: flex; flex-direction: column; max-height: calc(100vh - 32px); }
    .cc-log-head {
        align-items: flex-start;
        border-bottom: 1px solid var(--cc-separator);
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 18px 20px 14px;
    }
    .cc-log-head h5 { font-size: 17px; font-weight: 600; margin: 0; }
    .cc-log-head p { color: var(--cc-label-2); font-size: 13px; margin: 2px 0 0; }
    .cc-log-close {
        background: var(--cc-fill);
        border: 0;
        border-radius: 50%;
        color: var(--cc-label-2);
        flex: none;
        font-size: 18px;
        height: 30px;
        line-height: 1;
        width: 30px;
    }
    .cc-log-body { overflow-y: auto; padding: 16px 20px; }
    .cc-log-group + .cc-log-group { margin-top: 16px; }
    .cc-log-group > label, .cc-log-group > .cc-log-label {
        color: var(--cc-label-2);
        display: block;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .02em;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    .cc-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .cc-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .cc-chip span {
        background: var(--cc-fill);
        border-radius: 999px;
        box-shadow: inset 0 0 0 1px transparent;
        cursor: pointer;
        display: inline-block;
        font-size: 13px;
        font-weight: 500;
        padding: 7px 12px;
        transition: background .15s, box-shadow .15s, color .15s;
        user-select: none;
    }
    .cc-chip span:hover { background: var(--cc-fill-2); }
    .cc-chip input:checked + span { background: var(--cc-blue-tint); box-shadow: inset 0 0 0 1.5px var(--cc-blue); color: var(--cc-blue); }
    .cc-chip input:focus-visible + span { outline: 2px solid var(--cc-blue); outline-offset: 2px; }
    .cc-log-row { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; }
    .cc-log-hint { color: var(--cc-label-3); font-size: 12px; margin-top: 4px; }
    .cc-log-error { background: var(--cc-red-tint, #fdecea); border-radius: 10px; color: var(--cc-red, #d70015); font-size: 13px; margin-bottom: 12px; padding: 10px 12px; }
    .cc-log-foot {
        border-top: 1px solid var(--cc-separator);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding: 14px 20px;
    }
    .cc-log-alert-tag { background: var(--cc-fill); border-radius: 8px; display: inline-block; font-size: 12px; margin-top: 6px; padding: 3px 8px; }
    .cc-log-recent { background: var(--cc-fill); border-radius: 12px; margin-bottom: 16px; padding: 12px 14px; }
    .cc-log-recent.is-today { background: var(--cc-orange-tint); }
    .cc-log-recent-warning { color: var(--cc-orange); font-size: 13px; font-weight: 600; margin-bottom: 8px; }
    .cc-log-recent ul { list-style: none; margin: 0; padding: 0; }
    .cc-log-recent li { font-size: 13px; padding: 6px 0; }
    .cc-log-recent li + li { border-top: 1px solid var(--cc-separator); }
    .cc-log-recent .cc-log-recent-head { align-items: center; display: flex; flex-wrap: wrap; gap: 6px; }
    .cc-log-recent .cc-log-recent-note { color: var(--cc-label-2); margin-top: 2px; white-space: pre-line; }
    .cc-log-recent-foot { display: flex; justify-content: space-between; font-size: 12px; margin-top: 6px; }
    @media (max-width: 560px) { .cc-log-row { grid-template-columns: 1fr; } }
</style>

<dialog class="cc-log-dialog" id="cc-log-dialog" aria-labelledby="cc-log-title" data-history-base="{{ url('call-center/appels/historique') }}">
    <form method="POST" action="{{ route('call-center.interactions.store') }}" id="cc-log-form" novalidate>
        @csrf
        <input type="hidden" name="cible_type">
        <input type="hidden" name="cible_id">
        <input type="hidden" name="alert_id">

        <div class="cc-log-head">
            <div>
                <h5 id="cc-log-title">Enregistrer un appel</h5>
                <p id="cc-log-subtitle"></p>
                <span class="cc-log-alert-tag" id="cc-log-alert" hidden></span>
            </div>
            <button type="button" class="cc-log-close" data-cc-log-close aria-label="Fermer">&times;</button>
        </div>

        <div class="cc-log-body">
            <div class="cc-log-recent" id="cc-log-recent" hidden>
                <div class="cc-log-recent-warning" id="cc-log-recent-warning" hidden></div>
                <span class="cc-log-label">Derniers appels</span>
                <ul id="cc-log-recent-list"></ul>
                <div class="cc-log-recent-foot">
                    <span class="cc-muted" id="cc-log-recent-status"></span>
                    <a href="#" id="cc-log-recent-link" target="_blank" rel="noopener">Tout l'historique</a>
                </div>
            </div>

            <div class="cc-log-error" id="cc-log-error" hidden></div>

            <div class="cc-log-group">
                <span class="cc-log-label">Canal</span>
                <div class="cc-chips">
                    @foreach(CallCenterOutcomes::CANAUX as $key => $label)
                        <label class="cc-chip"><input type="radio" name="canal" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>

            <div class="cc-log-group">
                <span class="cc-log-label">Motif</span>
                <div class="cc-chips">
                    @foreach(CallCenterOutcomes::MOTIFS as $key => $label)
                        <label class="cc-chip"><input type="radio" name="motif" value="{{ $key }}"><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>

            <div class="cc-log-group">
                <span class="cc-log-label">Resultat</span>
                <div class="cc-chips">
                    @foreach(CallCenterOutcomes::resultatLabels() as $key => $label)
                        <label class="cc-chip"><input type="radio" name="resultat" value="{{ $key }}"><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>

            <div class="cc-log-group cc-log-row">
                <div>
                    <label for="cc-log-rappel">Rappeler le</label>
                    <input type="datetime-local" class="form-control" id="cc-log-rappel" name="rappel_le">
                    <div class="cc-log-hint" id="cc-log-rappel-hint">Facultatif. Obligatoire pour « A rappeler ».</div>
                </div>
                <div>
                    <label for="cc-log-duree">Duree (minutes)</label>
                    <input type="number" class="form-control" id="cc-log-duree" name="duree_minutes" min="0" max="600" inputmode="numeric" placeholder="ex. 3">
                </div>
            </div>

            <div class="cc-log-group">
                <label for="cc-log-commentaire">Commentaire</label>
                <textarea class="form-control" id="cc-log-commentaire" name="commentaire" rows="3" maxlength="2000" style="resize: vertical;" placeholder="Ce qui a ete dit, la suite a donner..."></textarea>
            </div>
        </div>

        <div class="cc-log-foot">
            <button type="button" class="btn btn-outline-secondary" data-cc-log-close>Annuler</button>
            <button type="submit" class="btn btn-primary" id="cc-log-submit">Enregistrer l'appel</button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        var dialog = document.getElementById('cc-log-dialog');
        var form = document.getElementById('cc-log-form');
        if (!dialog || !form || typeof dialog.showModal !== 'function') return;

        var errorBox = document.getElementById('cc-log-error');
        var submit = document.getElementById('cc-log-submit');
        var rappel = document.getElementById('cc-log-rappel');
        var alertTag = document.getElementById('cc-log-alert');

        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function localValue(date) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        }

        function showError(message) {
            errorBox.textContent = message;
            errorBox.hidden = !message;
            if (message) errorBox.scrollIntoView({ block: 'nearest' });
        }

        var recent = document.getElementById('cc-log-recent');
        var recentList = document.getElementById('cc-log-recent-list');
        var recentWarning = document.getElementById('cc-log-recent-warning');
        var recentStatus = document.getElementById('cc-log-recent-status');
        var loadToken = 0;

        function el(tag, className, text) {
            var node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined && text !== null) node.textContent = text;
            return node;
        }

        // Montre a l'agent qui a deja appele ce contact, et quand, avant qu'il compose le numero.
        function loadRecent(data) {
            var url = dialog.getAttribute('data-history-base') + '/' + encodeURIComponent(data.cible_type) + '/' + encodeURIComponent(data.cible_id);
            var token = ++loadToken;
            document.getElementById('cc-log-recent-link').href = url;
            recentList.innerHTML = '';
            recentWarning.hidden = true;
            recent.classList.remove('is-today');
            recentStatus.textContent = 'Chargement...';
            recent.hidden = false;

            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (response) { return response.ok ? response.json() : { appels: [] }; })
                .then(function (body) {
                    if (token !== loadToken) return;
                    var appels = body.appels || [];
                    if (!appels.length) {
                        recentStatus.textContent = 'Aucun appel enregistre pour ce contact.';
                        return;
                    }
                    recentStatus.textContent = '';
                    var today = new Date();
                    var todayLabel = pad(today.getDate()) + '/' + pad(today.getMonth() + 1) + '/' + today.getFullYear();
                    if (appels[0].date.indexOf(todayLabel) === 0) {
                        recent.classList.add('is-today');
                        recentWarning.textContent = 'Deja contacte aujourd\'hui par ' + appels[0].agent + ' (' + appels[0].il_y_a + ').';
                        recentWarning.hidden = false;
                    }
                    appels.forEach(function (appel) {
                        var li = el('li');
                        var head = el('div', 'cc-log-recent-head');
                        var badge = el('span', 'badge badge-' + appel.tone, appel.resultat);
                        head.appendChild(badge);
                        head.appendChild(el('span', '', appel.date + ' · ' + appel.agent + ' · ' + appel.canal + (appel.alerte ? ' · ' + appel.alerte : '')));
                        li.appendChild(head);
                        if (appel.rappel) li.appendChild(el('div', 'cc-log-recent-note', 'Rappel prevu le ' + appel.rappel));
                        if (appel.commentaire) li.appendChild(el('div', 'cc-log-recent-note', appel.commentaire));
                        recentList.appendChild(li);
                    });
                })
                .catch(function () {
                    if (token === loadToken) recentStatus.textContent = 'Historique indisponible.';
                });
        }

        function open(data) {
            form.reset();
            showError('');
            form.cible_type.value = data.cible_type || '';
            form.cible_id.value = data.cible_id || '';
            form.alert_id.value = data.alert_id || '';
            document.getElementById('cc-log-subtitle').textContent = data.nom || '';
            alertTag.hidden = !data.alerte;
            alertTag.textContent = data.alerte ? 'Alerte : ' + data.alerte : '';
            var motif = form.querySelector('input[name="motif"][value="' + (data.motif || (data.alert_id ? 'relance_alerte' : 'prospection')) + '"]');
            if (motif) motif.checked = true;
            rappel.min = localValue(new Date());
            loadRecent(data);
            document.querySelectorAll('.cc-actions-dropdown[open]').forEach(function (d) { d.removeAttribute('open'); });
            dialog.showModal();
        }

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-cc-log]');
            if (trigger) {
                event.preventDefault();
                try { open(JSON.parse(trigger.getAttribute('data-cc-log'))); } catch (e) { /* donnees invalides */ }
                return;
            }
            if (event.target.closest('[data-cc-log-close]') || event.target === dialog) dialog.close();
        });

        // "A rappeler" : propose demain 9h si aucune date n'est saisie.
        form.addEventListener('change', function (event) {
            if (event.target.name === 'resultat' && event.target.value === 'a_rappeler' && !rappel.value) {
                var tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(9, 0, 0, 0);
                rappel.value = localValue(tomorrow);
            }
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!form.querySelector('input[name="motif"]:checked')) return showError('Choisissez le motif de l\'appel.');
            if (!form.querySelector('input[name="resultat"]:checked')) return showError('Choisissez le resultat de l\'appel.');
            if (form.querySelector('input[name="resultat"]:checked').value === 'a_rappeler' && !rappel.value) return showError('Indiquez la date du rappel.');

            submit.disabled = true;
            showError('');
            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (body) {
                    if (response.ok) {
                        window.location.reload();
                        return;
                    }
                    var errors = body.errors ? Object.keys(body.errors).map(function (key) { return body.errors[key][0]; }) : [];
                    showError(errors.length ? errors.join(' ') : (body.message || 'Enregistrement impossible. Reessayez.'));
                    submit.disabled = false;
                });
            }).catch(function () {
                showError('Connexion impossible. Verifiez votre reseau et reessayez.');
                submit.disabled = false;
            });
        });
    })();
</script>
