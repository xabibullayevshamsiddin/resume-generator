/*
 * Ma'lumotnoma generatori — forma mantıq'i (vanilla JS).
 * 1) Rasm preview  2) Dinamik qatorlar  3) "Boshqa" toggle
 * 4) Client-side A4 preview (serverga so'rov yuborilmaydi)
 * 5) Submit himoyasi  6) Formani tozalash
 */
(function () {
    'use strict';

    var MAX_PHOTO_SIZE = 3 * 1024 * 1024; // 3 MB
    var MAX_EMPLOYMENT = 20;
    var MAX_RELATIVES = 10;

    function $(selector, root) {
        return (root || document).querySelector(selector);
    }

    function $all(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    /* =====================================================================
     * 1) Profil rasmi preview + hajm tekshiruvi
     * ===================================================================== */
    var photoInput = $('#photo');
    var photoPreview = $('#js-photo-preview');
    var photoPreviewImg = $('#js-photo-preview-img');

    function readPhotoAsDataUrl(callback) {
        var file = photoInput && photoInput.files && photoInput.files[0];

        if (!file) {
            callback('');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            callback(event.target.result);
        };
        reader.readAsDataURL(file);
    }

    if (photoInput && photoPreviewImg) {
        photoInput.addEventListener('change', function () {
            var file = photoInput.files && photoInput.files[0];

            if (!file) {
                photoPreview.classList.add('is-hidden');
                photoPreviewImg.removeAttribute('src');
                return;
            }

            if (file.size > MAX_PHOTO_SIZE) {
                alert('Profil rasmi 3 MB dan oshmasligi kerak. Hozirgi hajm: ' +
                    (file.size / 1024 / 1024).toFixed(1) + ' MB');
                photoInput.value = '';
                photoPreview.classList.add('is-hidden');
                photoPreviewImg.removeAttribute('src');
                return;
            }

            readPhotoAsDataUrl(function (dataUrl) {
                if (dataUrl) {
                    photoPreviewImg.src = dataUrl;
                    photoPreview.classList.remove('is-hidden');
                }
            });
        });
    }

    /* =====================================================================
     * 2) Dinamik qatorlar (mehnat faoliyati va qarindoshlar)
     * ===================================================================== */
    function reindexRows(tbody, prefix) {
        $all('tr', tbody).forEach(function (tr, index) {
            tr.setAttribute('data-row', String(index));

            $all('input, select, textarea', tr).forEach(function (element) {
                var name = element.getAttribute('name');

                if (name) {
                    element.setAttribute('name', name.replace(
                        new RegExp('^' + prefix + '\\[\\d+\\]'),
                        prefix + '[' + index + ']'
                    ));
                }

                var id = element.getAttribute('id');
                if (id) {
                    element.setAttribute('id', id.replace(/-\d+-/, '-' + index + '-'));
                }
            });

            $all('label', tr).forEach(function (label) {
                var htmlFor = label.getAttribute('for');
                if (htmlFor) {
                    label.setAttribute('for', htmlFor.replace(/-\d+-/, '-' + index + '-'));
                }
            });
        });
    }

    function getRowIndex(tr) {
        return parseInt(tr.getAttribute('data-row'), 10) || 0;
    }

    function addRow(tbody, templateId, prefix, maxRows, afterAdd) {
        var current = $all('tr', tbody).length;

        if (current >= maxRows) {
            alert('Ko\'pi bilan ' + maxRows + ' qator qo\'shish mumkin.');
            return;
        }

        var template = document.getElementById(templateId);

        if (!template) {
            return;
        }

        var maxExisting = -1;
        $all('tr', tbody).forEach(function (tr) {
            maxExisting = Math.max(maxExisting, getRowIndex(tr));
        });

        var newIndex = maxExisting + 1;
        var html = template.textContent.split('__INDEX__').join(String(newIndex));

        var temp = document.createElement('tbody');
        temp.innerHTML = html.trim();
        var row = temp.firstElementChild;

        tbody.appendChild(row);

        if (typeof afterAdd === 'function') {
            afterAdd(row);
        }

        reindexRows(tbody, prefix);
    }

    function bindRemoveButtons(tbody, prefix, minRows) {
        tbody.addEventListener('click', function (event) {
            var button = event.target.closest('.btn--remove-row');

            if (!button) {
                return;
            }

            if ($all('tr', tbody).length <= minRows) {
                alert('Kamida bitta qator qolishi kerak.');
                return;
            }

            var row = button.closest('tr');

            if (row) {
                row.remove();
            }

            reindexRows(tbody, prefix);
            syncOtherToggles();
        });
    }

    var employmentTbody = document.getElementById('js-employment-tbody');
    var relativesTbody = document.getElementById('js-relatives-tbody');

    if (employmentTbody) {
        bindRemoveButtons(employmentTbody, 'employment', 1);

        var addEmploymentButton = document.getElementById('js-add-employment');
        if (addEmploymentButton) {
            addEmploymentButton.addEventListener('click', function () {
                addRow(employmentTbody, 'js-employment-row-template', 'employment', MAX_EMPLOYMENT);
            });
        }

        var fillFirstButton = document.getElementById('js-fill-first-employment');
        if (fillFirstButton) {
            fillFirstButton.addEventListener('click', function () {
                var firstRow = $('tr', employmentTbody);

                if (!firstRow) {
                    return;
                }

                var period = document.getElementById('current_status');
                var organization = document.getElementById('current_organization');
                var position = document.getElementById('current_position');

                var periodInput = $('.cell-period input', firstRow);
                var organizationInput = $('.cell-organization input', firstRow);
                var positionInput = $('.cell-position input', firstRow);

                if (periodInput && period) {
                    periodInput.value = period.value;
                }
                if (organizationInput && organization) {
                    organizationInput.value = organization.value;
                }
                if (positionInput && position) {
                    positionInput.value = position.value;
                }
            });
        }
    }

    if (relativesTbody) {
        bindRemoveButtons(relativesTbody, 'relatives', 1);

        var addRelativeButton = document.getElementById('js-add-relative');
        if (addRelativeButton) {
            addRelativeButton.addEventListener('click', function () {
                addRow(relativesTbody, 'js-relative-row-template', 'relatives', MAX_RELATIVES, function (row) {
                    bindRelativeSelect(row);
                    syncOtherToggles();
                });
            });
        }
    }

    /* =====================================================================
     * 3) "Boshqa" tanlanganda qo'shimcha matn maydonini ko'rsatish
     * ===================================================================== */
    function bindRelativeSelect(row) {
        var select = $('.js-relative-relationship', row);

        if (select) {
            select.addEventListener('change', syncOtherToggles);
        }
    }

    function syncOtherToggles() {
        $all('select[data-other-target]').forEach(function (select) {
            var target = document.getElementById(select.getAttribute('data-other-target'));

            if (!target) {
                return;
            }

            if (select.value === 'boshqa') {
                target.classList.remove('is-hidden');
            } else {
                target.classList.add('is-hidden');
                var field = $('input', target);

                if (field && select.value !== '') {
                    field.value = '';
                }
            }
        });

        if (relativesTbody) {
            $all('tr', relativesTbody).forEach(function (row) {
                var select = $('.js-relative-relationship', row);
                var otherInput = $('.js-relationship-other', row);

                if (!select || !otherInput) {
                    return;
                }

                if (select.value === 'Boshqa') {
                    otherInput.classList.remove('is-hidden');
                } else {
                    otherInput.classList.add('is-hidden');
                }
            });
        }
    }

    $all('select[data-other-target]').forEach(function (select) {
        select.addEventListener('change', syncOtherToggles);
    });

    if (relativesTbody) {
        $all('tr', relativesTbody).forEach(bindRelativeSelect);
    }

    // Sahifa yuklanganda old() qiymatlarini hisobga olish
    syncOtherToggles();

    /* =====================================================================
     * 4) Client-side A4 preview (serverga so'rov yuborilmaydi)
     * ===================================================================== */
    var previewRoot = document.getElementById('js-preview-root');
    var previewPages = document.getElementById('js-preview-pages');
    var previewButton = document.getElementById('js-preview-btn');
    var previewClose = document.getElementById('js-preview-close');

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getFormValue(name) {
        var element = document.querySelector('[name="' + name + '"]');

        if (!element) {
            return '';
        }

        return element.value == null ? '' : String(element.value);
    }

    function valueOrDefault(value) {
        var trimmed = String(value == null ? '' : value).trim();
        return trimmed === '' ? 'yo\'q' : trimmed;
    }

    function dashIfEmpty(value) {
        var trimmed = String(value == null ? '' : value).trim();
        return trimmed === '' ? '—' : trimmed;
    }

    function formatDate(value) {
        var trimmed = String(value == null ? '' : value).trim();

        if (trimmed === '') {
            return '—';
        }

        var parts = trimmed.split('-');

        if (parts.length === 3 && parts[0].length === 4) {
            return parts[2] + '.' + parts[1] + '.' + parts[0];
        }

        return trimmed;
    }

    function withOther(value, other) {
        if (value === 'boshqa') {
            var trimmed = String(other || '').trim();
            return trimmed === '' ? 'Boshqa' : 'Boshqa: ' + trimmed;
        }

        return valueOrDefault(value);
    }

    function withOtherRow(value, other) {
        if (value === 'Boshqa') {
            var trimmed = String(other || '').trim();
            return trimmed === '' ? 'Boshqa' : 'Boshqa: ' + trimmed;
        }

        return value;
    }

    function cellValue(row, selector) {
        var input = $(selector, row);
        return input && input.value ? String(input.value).trim() : '';
    }

    function collectEmploymentRows() {
        return $all('tr', employmentTbody).map(function (tr) {
            return {
                period: dashIfEmpty(cellValue(tr, '.cell-period input')),
                organization: dashIfEmpty(cellValue(tr, '.cell-organization input')),
                position: cellValue(tr, '.cell-position input')
            };
        });
    }

    function collectRelativeRows() {
        return $all('tr', relativesTbody).map(function (tr) {
            var select = $('.js-relative-relationship', tr);
            var other = $('.js-relationship-other', tr);

            return {
                relationship: withOtherRow(select ? select.value : '', other ? other.value : ''),
                fullName: dashIfEmpty(cellValue(tr, '.cell-fullname input')),
                birthMain: dashIfEmpty(cellValue(tr, '.cell-birth-year input')),
                birthSub: dashIfEmpty(cellValue(tr, '.cell-birth-place input')),
                workMain: dashIfEmpty(cellValue(tr, '.cell-workplace input')),
                workSub: dashIfEmpty(cellValue(tr, '.cell-position input')),
                addressMain: dashIfEmpty(cellValue(tr, '.cell-address input')),
                addressSub: dashIfEmpty(cellValue(tr, '.cell-phone input'))
            };
        });
    }

    function personalRow(labelA, valueA, labelB, valueB) {
        return '<tr>' +
            '<td class="doc__label">' + escapeHtml(labelA) + '</td>' +
            '<td class="doc__value">' + escapeHtml(valueA) + '</td>' +
            '<td class="doc__label">' + escapeHtml(labelB) + '</td>' +
            '<td class="doc__value">' + escapeHtml(valueB) + '</td>' +
            '</tr>';
    }

    function finalRow(label, value) {
        return '<tr>' +
            '<td class="doc__label">' + escapeHtml(label) + '</td>' +
            '<td class="doc__value">' + escapeHtml(value) + '</td>' +
            '</tr>';
    }

    function buildPageOneHtml(photoSrc) {
        var status = dashIfEmpty(getFormValue('current_status'));
        var organization = dashIfEmpty(getFormValue('current_organization'));
        var position = dashIfEmpty(getFormValue('current_position'));

        var statusLine = [status, organization, position].filter(function (part) {
            return part !== '—';
        }).join(', ');

        var institution = valueOrDefault(getFormValue('institution'));
        var institutionYear = String(getFormValue('institution_year') || '').trim();
        var completed = institutionYear === '' ? institution : institution + ', ' + institutionYear;

        var photoHtml = photoSrc
            ? '<img class="doc__photo" src="' + escapeHtml(photoSrc) + '" alt="Profil rasmi">'
            : '<div class="doc__photo doc__photo--empty"></div>';

        var employmentRows = collectEmploymentRows().map(function (row) {
            return '<tr>' +
                '<td class="doc__td doc__nowrap">' + escapeHtml(row.period) + '</td>' +
                '<td class="doc__td">' + escapeHtml(row.organization) +
                (row.position !== '' ? ', ' + escapeHtml(row.position) : '') +
                '</td></tr>';
        }).join('');

        return [
            '<div class="doc__page doc__page--1">',
            '<table class="doc__header-table">',
            '<tr>',
            '<td class="doc__header-name">',
            '<div class="doc__fullname">' + escapeHtml(getFormValue('full_name') || '—') + '</div>',
            '<div class="doc__status">' + escapeHtml(statusLine || '—') + '</div>',
            '</td>',
            '<td class="doc__header-photo" rowspan="2">' + photoHtml + '</td>',
            '</tr>',
            '</table>',
            '<div class="doc__section-title">Shaxsiy ma\'lumotlar</div>',
            '<table class="doc__personal-table">',
            personalRow('Tug\'ilgan sanasi:', formatDate(getFormValue('birth_date')), 'Millati:', valueOrDefault(getFormValue('nationality'))),
            personalRow('Tug\'ilgan joyi:', valueOrDefault(getFormValue('birth_place')), 'Partiyaviyligi:', withOther(getFormValue('party_affiliation'), getFormValue('party_affiliation_other'))),
            personalRow('Ma\'lumoti:', valueOrDefault(getFormValue('education')), 'Harbiy unvoni:', valueOrDefault(getFormValue('military_rank'))),
            personalRow('Tamomlagan:', completed, 'Ilmiy darajasi:', withOther(getFormValue('academic_degree'), getFormValue('academic_degree_other'))),
            personalRow('Mutaxassisligi:', valueOrDefault(getFormValue('specialty')), 'Ilmiy unvoni:', withOther(getFormValue('academic_title'), getFormValue('academic_title_other'))),
            personalRow('Chet tillari:', valueOrDefault(getFormValue('languages')), 'Mukofotlari:', valueOrDefault(getFormValue('awards'))),
            '</table>',
            '<div class="doc__section-title">MEHNAT FAOLIYATI</div>',
            '<table class="doc__employment-table">',
            '<thead><tr><th class="doc__th" style="width:30%">Davri</th><th class="doc__th">Tashkilot, lavozim</th></tr></thead>',
            '<tbody>' + employmentRows + '</tbody>',
            '</table>',
            '</div>'
        ].join('\n');
    }

    function buildPageTwoHtml() {
        var rows = collectRelativeRows().map(function (row) {
            return '<tr>' +
                '<td class="doc__td">' + escapeHtml(row.relationship) + '</td>' +
                '<td class="doc__td">' + escapeHtml(row.fullName) + '</td>' +
                '<td class="doc__td">' + escapeHtml(row.birthMain) +
                '<span class="doc__sub">' + escapeHtml(row.birthSub) + '</span></td>' +
                '<td class="doc__td">' + escapeHtml(row.workMain) +
                '<span class="doc__sub">' + escapeHtml(row.workSub) + '</span></td>' +
                '<td class="doc__td">' + escapeHtml(row.addressMain) +
                '<span class="doc__sub">' + escapeHtml(row.addressSub) + '</span></td>' +
                '</tr>';
        }).join('');

        var fullName = getFormValue('full_name') || '—';

        return [
            '<div class="doc__page doc__page--2">',
            '<div class="doc__page2-title">' + escapeHtml(fullName) + 'ning yaqin qarindoshlari haqida MA\'LUMOT</div>',
            '<table class="doc__relatives-table">',
            '<thead><tr>',
            '<th class="doc__th" style="width:14%">Qarindoshligi</th>',
            '<th class="doc__th" style="width:22%">F.I.Sh.</th>',
            '<th class="doc__th" style="width:20%">Tug\'ilgan yili, joyi</th>',
            '<th class="doc__th" style="width:22%">Ish joyi, lavozimi</th>',
            '<th class="doc__th" style="width:22%">Turar joyi, telefon</th>',
            '</tr></thead>',
            '<tbody>' + rows + '</tbody>',
            '</table>',
            '<table class="doc__final-table">',
            finalRow('Mobil raqami:', dashIfEmpty(getFormValue('phone'))),
            finalRow('Uy manzili:', valueOrDefault(getFormValue('home_address'))),
            finalRow('Pasport ma\'lumotlari:', dashIfEmpty(getFormValue('passport_info'))),
            '</table>',
            '</div>'
        ].join('\n');
    }

    function renderPreview(photoSrc) {
        // .doc--preview.doc--continuous — hujjat bitta uzluksiz sahifada ko'rinadi
        previewPages.innerHTML =
            '<div class="doc doc--preview doc--continuous">' +
            buildPageOneHtml(photoSrc) +
            buildPageTwoHtml() +
            '</div>';
        previewRoot.classList.remove('is-hidden');
        previewRoot.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closePreview() {
        previewRoot.classList.add('is-hidden');
        previewRoot.hidden = true;
        previewPages.innerHTML = '';
        document.body.style.overflow = '';
    }

    if (previewButton && previewRoot && previewPages) {
        previewButton.addEventListener('click', function () {
            readPhotoAsDataUrl(renderPreview);
        });

        if (previewClose) {
            previewClose.addEventListener('click', closePreview);
        }

        previewRoot.addEventListener('click', function (event) {
            if (event.target === previewRoot) {
                closePreview();
            }
        });
    }

    /* =====================================================================
     * 5) Submit himoyasi: disable + ikki marta jo'natishni cheklash
     * ===================================================================== */
    var form = document.getElementById('js-resume-form');
    var submitButton = document.getElementById('js-submit-btn');
    var isSubmitting = false;

    if (form && submitButton) {
        form.addEventListener('submit', function () {
            if (isSubmitting) {
                return;
            }

            isSubmitting = true;
            submitButton.disabled = true;
            submitButton.textContent = 'PDF tayyorlanmoqda...';
        });
    }

    /* =====================================================================
     * 6) Formani tozalash (confirmation bilan)
     * ===================================================================== */
    var resetButton = document.getElementById('js-reset-btn');

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (window.confirm('Formani tozalamoqchimisiz? Barcha kiritilgan ma\'lumotlar o\'chiriladi.')) {
                window.location.href = window.location.pathname;
            }
        });
    }
})();
