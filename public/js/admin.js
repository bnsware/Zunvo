document.addEventListener('DOMContentLoaded', function () {
    initAdminShell();
    initAdminConfirm();
    initCategoryAdmin();
    initAdminIconPicker();
    initAdminNavSearch();
});

function initAdminShell() {
    var menuBtn = document.getElementById('zv-menu-btn');
    var aside = document.getElementById('zv-aside');
    var backdrop = document.getElementById('zv-backdrop');
    if (!menuBtn || !aside || !backdrop) return;

    function closeMenu() {
        aside.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        backdrop.hidden = true;
    }

    function openMenu() {
        aside.classList.add('is-open');
        backdrop.classList.add('is-open');
        backdrop.hidden = false;
    }

    menuBtn.addEventListener('click', function () {
        if (aside.classList.contains('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    backdrop.addEventListener('click', closeMenu);
}

function initAdminConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
}

function initCategoryAdmin() {
    var drawer = document.getElementById('category-drawer');
    var editForm = document.getElementById('category-edit-form');
    var addForm = document.getElementById('category-add-form');

    [addForm, editForm].forEach(function (form) {
        if (!form) return;
        bindForumTypeFields(form);
    });

    document.querySelectorAll('[data-category-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var node = btn.closest('[data-category]');
            if (!node || !drawer || !editForm) return;
            var data;
            try {
                data = JSON.parse(node.getAttribute('data-category'));
            } catch (e) {
                return;
            }
            fillCategoryForm(editForm, data);
            drawer.hidden = false;
            document.body.classList.add('zv-drawer-open');
        });
    });

    document.querySelectorAll('[data-drawer-close]').forEach(function (el) {
        el.addEventListener('click', function () {
            if (!drawer) return;
            drawer.hidden = true;
            document.body.classList.remove('zv-drawer-open');
        });
    });

    if (editForm) {
        editForm.addEventListener('submit', function () {
            var parent = editForm.querySelector('[data-parent-select]');
            if (parent) {
                parent.disabled = false;
            }
            var canCheck = editForm.querySelector('[data-can-create-check]');
            if (canCheck) {
                canCheck.disabled = false;
            }
        });
    }
}

function bindForumTypeFields(form) {
    var typeSelect = form.querySelector('[data-forum-type-select]');
    var parentWrap = form.querySelector('[data-parent-wrap]');
    var canWrap = form.querySelector('[data-can-create-wrap]');
    if (!typeSelect) return;

    function sync() {
        var isSection = typeSelect.value === 'section';
        var isAddForm = form.id === 'category-add-form';
        if (parentWrap) {
            parentWrap.style.display = isSection ? 'none' : '';
            var parentSelect = parentWrap.querySelector('[data-parent-select]');
            if (parentSelect) {
                if (isSection) {
                    parentSelect.disabled = true;
                    parentSelect.removeAttribute('required');
                } else {
                    parentSelect.disabled = false;
                    if (isAddForm) {
                        parentSelect.setAttribute('required', 'required');
                    } else {
                        parentSelect.removeAttribute('required');
                    }
                }
            }
        }
        if (canWrap) {
            canWrap.style.display = isSection ? 'none' : '';
            var check = canWrap.querySelector('[data-can-create-check]');
            if (check) {
                check.disabled = isSection;
                if (isSection) {
                    check.checked = false;
                }
            }
        }
    }

    typeSelect.addEventListener('change', sync);
    sync();
}

function ensureParentOption(select, parentId, parentName) {
    if (!select || !parentId) return;
    var val = String(parentId);
    var found = false;
    Array.prototype.forEach.call(select.options, function (opt) {
        if (opt.value === val) found = true;
    });
    if (!found) {
        var opt = document.createElement('option');
        opt.value = val;
        opt.textContent = parentName || ('Üst bölüm #' + val);
        select.appendChild(opt);
    }
    select.value = val;
}

function fillCategoryForm(form, data) {
    var idInput = form.querySelector('#edit-category-id');
    if (idInput) idInput.value = data.id || '';

    var name = form.querySelector('[name="name"]');
    if (name) name.value = data.name || '';

    var desc = form.querySelector('[name="description"]');
    if (desc) desc.value = data.description || '';

    var type = form.querySelector('[name="forum_type"]');
    if (type) type.value = data.forum_type || 'forum';

    var typeSelect = form.querySelector('[data-forum-type-select]');
    if (typeSelect) {
        typeSelect.dispatchEvent(new Event('change'));
    }

    var parent = form.querySelector('[data-parent-select]');
    if (parent) {
        if (data.parent_id) {
            parent.disabled = false;
            ensureParentOption(parent, data.parent_id, data.parent_name || '');
            parent.value = String(data.parent_id);
        } else {
            parent.value = '';
        }
    }

    var order = form.querySelector('[name="order_num"]');
    if (order) order.value = data.order_num || 0;

    var color = form.querySelector('[name="color"]');
    if (color) color.value = data.color || '#0d9488';

    var canCheck = form.querySelector('[data-can-create-check]');
    if (canCheck) canCheck.checked = !!data.can_create_topic;

    var iconInput = form.querySelector('[data-icon-value]');
    var iconPreview = form.querySelector('[data-icon-preview]');
    var iconName = form.querySelector('[data-icon-name]');
    if (iconInput) {
        iconInput.value = data.icon || 'folder';
    }
    if (iconName) {
        iconName.textContent = data.icon || 'folder';
    }
    if (iconPreview && data.icon && window.ZUNVO_CONFIG) {
        var paths = form.querySelector('[data-icon-picker]');
        if (paths) {
            var opt = document.querySelector('[data-icon-option="' + data.icon + '"]');
            if (opt) {
                var svg = opt.querySelector('svg');
                if (svg) {
                    iconPreview.innerHTML = svg.outerHTML;
                }
            }
        }
    }
}

function initAdminNavSearch() {
    var input = document.getElementById('zv-nav-search');
    var nav = document.getElementById('zv-nav');
    if (!input || !nav) return;

    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        nav.querySelectorAll('.zv-nav-item').forEach(function (link) {
            var label = (link.getAttribute('data-nav-label') || link.textContent || '').toLowerCase();
            link.classList.toggle('is-hidden', q !== '' && label.indexOf(q) === -1);
        });
        nav.querySelectorAll('[data-nav-fold]').forEach(function (section) {
            if (!q) return;
            var visible = section.querySelectorAll('.zv-nav-item:not(.is-hidden)').length > 0;
            if (visible) section.setAttribute('open', '');
        });
        var group = nav.querySelector('[data-nav-group]');
        if (group && q) {
            var any = group.querySelectorAll('.zv-nav-item:not(.is-hidden)').length > 0;
            group.style.display = any ? '' : 'none';
        } else if (group) {
            group.style.display = '';
        }
    });
}

function initAdminIconPicker() {
    var modal = document.getElementById('admin-icon-modal');
    if (!modal) return;

    var activePicker = null;
    var searchInput = modal.querySelector('[data-icon-search]');
    var grid = modal.querySelector('[data-icon-grid]');
    var tabs = modal.querySelectorAll('[data-icon-category]');
    var options = modal.querySelectorAll('[data-icon-option]');
    var sections = modal.querySelectorAll('[data-icon-section]');
    var activeCategory = '';

    function openModal(picker) {
        activePicker = picker;
        var current = picker.querySelector('[data-icon-value]').value;
        options.forEach(function (opt) {
            opt.classList.toggle('is-selected', opt.getAttribute('data-icon-option') === current);
        });
        modal.hidden = false;
        document.body.classList.add('admin-icon-modal-open');
        if (searchInput) {
            searchInput.value = '';
            filterIcons('');
        }
        setCategory('Genel');
        if (searchInput) {
            setTimeout(function () { searchInput.focus(); }, 50);
        }
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('admin-icon-modal-open');
        activePicker = null;
    }

    function setCategory(category) {
        activeCategory = category;
        tabs.forEach(function (tab) {
            tab.classList.toggle('is-active', tab.getAttribute('data-icon-category') === category);
        });
        sections.forEach(function (section) {
            if (category === '__all') {
                section.classList.remove('is-hidden');
            } else {
                section.classList.toggle('is-hidden', section.getAttribute('data-icon-section') !== category);
            }
        });
        filterIcons(searchInput ? searchInput.value : '');
    }

    function filterIcons(query) {
        query = (query || '').trim().toLowerCase();
        options.forEach(function (opt) {
            var slug = (opt.getAttribute('data-icon-option') || '').toLowerCase();
            var label = (opt.getAttribute('data-icon-label') || '').toLowerCase();
            var cat = opt.getAttribute('data-icon-category') || '';
            var matchesQuery = !query || slug.indexOf(query) !== -1 || label.indexOf(query) !== -1;
            var matchesCategory = activeCategory === '__all' || cat === activeCategory;
            var section = opt.closest('[data-icon-section]');
            var sectionVisible = !section || !section.classList.contains('is-hidden');
            opt.classList.toggle('is-hidden', !(matchesQuery && matchesCategory && sectionVisible));
        });
        sections.forEach(function (section) {
            if (activeCategory !== '__all' && section.getAttribute('data-icon-section') !== activeCategory) {
                return;
            }
            var visible = section.querySelectorAll('.admin-icon-option:not(.is-hidden)').length > 0;
            section.classList.toggle('is-hidden', !visible && query !== '');
        });
    }

    function selectIcon(slug, label, svgHtml) {
        if (!activePicker) return;
        var input = activePicker.querySelector('[data-icon-value]');
        var preview = activePicker.querySelector('[data-icon-preview]');
        var nameEl = activePicker.querySelector('[data-icon-name]');
        if (input) input.value = slug;
        if (preview) preview.innerHTML = svgHtml;
        if (nameEl) nameEl.textContent = label;
        closeModal();
    }

    document.addEventListener('click', function (e) {
        var openBtn = e.target.closest('[data-icon-open]');
        if (openBtn) {
            var picker = openBtn.closest('[data-icon-picker]');
            if (picker) {
                e.preventDefault();
                openModal(picker);
            }
        }
    });

    modal.querySelectorAll('[data-icon-close]').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            if (activeCategory !== '__all' && this.value.trim()) {
                setCategory('__all');
            }
            filterIcons(this.value);
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            setCategory(tab.getAttribute('data-icon-category'));
        });
    });

    if (grid) {
        grid.addEventListener('click', function (e) {
            var opt = e.target.closest('[data-icon-option]');
            if (!opt || opt.classList.contains('is-hidden')) return;
            var slug = opt.getAttribute('data-icon-option');
            var label = opt.getAttribute('data-icon-label') || slug;
            var svg = opt.querySelector('svg');
            selectIcon(slug, label, svg ? svg.outerHTML : '');
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
}
