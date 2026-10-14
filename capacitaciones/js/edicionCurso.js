(function ($) {
    "use strict";

    $(function () {
        const moduleModalElement = document.getElementById("module-form-modal");
        const sectionModalElement = document.getElementById("section-form-modal");
        const resourceModalElement = document.getElementById("resource-form-modal");
        const deleteModalElement = document.getElementById("delete-editor-item-modal");
        const toastElement = document.getElementById("editor-feedback-toast");
        const moduleModal = getModalInstance(moduleModalElement);
        const sectionModal = getModalInstance(sectionModalElement);
        const resourceModal = getModalInstance(resourceModalElement);
        const deleteModal = getModalInstance(deleteModalElement);
        const feedbackToast = getToastInstance(toastElement);
        const moduleContentStates = new Map();
        const $initialTabContent = $(".editor-workspace > .editor-tab-content").first();
        const $courseResourcesPanel = $initialTabContent.find("#editor-panel-resources").detach();
        const $moduleContentTemplate = $initialTabContent.clone(false, false);
        attachCourseResourcesPanel($initialTabContent);
        let moduleFormMode = "add";
        let $moduleBeingEdited = $();
        let pendingDeletion = null;
        let currentModuleKey = null;
        let dynamicId = 100;

        function getModalInstance(element) {
            if (!element || !window.bootstrap || !window.bootstrap.Modal) {
                return null;
            }

            return window.bootstrap.Modal.getOrCreateInstance(element);
        }

        function getToastInstance(element) {
            if (!element || !window.bootstrap || !window.bootstrap.Toast) {
                return null;
            }

            return window.bootstrap.Toast.getOrCreateInstance(element, { delay: 3200 });
        }

        function escapeHtml(value) {
            const characters = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                "\"": "&quot;",
                "'": "&#039;"
            };

            return String(value == null ? "" : value).replace(/[&<>"']/g, function (character) {
                return characters[character];
            });
        }

        function sanitizeIcon(value, fallback) {
            const icon = String(value || "");
            return /^fa-[a-z0-9-]+$/i.test(icon) ? icon : (fallback || "fa-book-open");
        }

        function nextDynamicId(prefix) {
            dynamicId += 1;
            return prefix + "-" + dynamicId;
        }

        function ensureModuleKey($card) {
            let key = $card.attr("data-editor-module-key");

            if (!key) {
                key = nextDynamicId("module-state");
                $card.attr("data-editor-module-key", key);
            }

            return key;
        }

        function cloneContentState($source) {
            const $clone = $source.clone(false, false);
            const $sourceFields = $source.find(":input").filter(function () {
                return !$(this).closest("#editor-panel-resources").length;
            });

            $clone.find("#editor-panel-resources").remove();
            $sourceFields.each(function (index) {
                const $sourceField = $(this);
                const $cloneField = $clone.find(":input").eq(index);

                if ($sourceField.is(":checkbox, :radio")) {
                    $cloneField.prop("checked", $sourceField.prop("checked"));
                } else {
                    $cloneField.val($sourceField.val());
                }
            });

            return $clone;
        }

        function detachCourseResourcesPanel() {
            if ($courseResourcesPanel.parent().length) {
                $courseResourcesPanel.detach();
            }
        }

        function attachCourseResourcesPanel($tabContent) {
            const $quizPanel = $tabContent.find('[data-editor-panel="quiz"]').first();

            detachCourseResourcesPanel();
            if ($quizPanel.length) {
                $quizPanel.before($courseResourcesPanel);
            } else {
                $tabContent.append($courseResourcesPanel);
            }
        }

        function activateEditorTab(tabName) {
            const selectedTab = tabName || "data";

            $(".editor-tab-button").removeClass("is-active").attr("aria-selected", "false");
            $('.editor-tab-button[data-editor-tab="' + selectedTab + '"]')
                .addClass("is-active")
                .attr("aria-selected", "true");
            $(".editor-tab-panel").removeClass("is-active").prop("hidden", true);
            $('.editor-tab-panel[data-editor-panel="' + selectedTab + '"]')
                .addClass("is-active")
                .prop("hidden", false);
        }

        function showWorkspaceEmpty() {
            const $workspace = $(".editor-workspace");

            $workspace.addClass("is-empty");
            detachCourseResourcesPanel();
            $workspace.children(".editor-tab-content").remove();
            currentModuleKey = null;
        }

        function switchModuleContent($card) {
            const nextKey = ensureModuleKey($card);
            const $workspace = $(".editor-workspace");

            $workspace.removeClass("is-empty");
            if (currentModuleKey === nextKey && $workspace.children(".editor-tab-content").length) {
                activateEditorTab("data");
                return;
            }

            const $currentContent = $workspace.children(".editor-tab-content").first();
            if (!currentModuleKey && $currentContent.length) {
                currentModuleKey = nextKey;
                attachCourseResourcesPanel($currentContent);
                activateEditorTab("data");
                return;
            }

            if (currentModuleKey && $currentContent.length) {
                detachCourseResourcesPanel();
                moduleContentStates.set(currentModuleKey, $currentContent.detach());
            }

            let $nextContent = moduleContentStates.get(nextKey);
            if ($nextContent && $nextContent.length) {
                moduleContentStates.delete(nextKey);
            } else {
                $nextContent = cloneContentState($moduleContentTemplate);
            }

            attachCourseResourcesPanel($nextContent);
            $workspace.children(".editor-tabs").after($nextContent);
            currentModuleKey = nextKey;
            activateEditorTab("data");
        }

        function readData($element, name) {
            return String($element.attr("data-" + name) || "");
        }

        function findField($form, names, ids) {
            let $field = $();

            $.each(names || [], function (_, name) {
                if (!$field.length) {
                    $field = $form.find('[name="' + name + '"]').first();
                }
            });

            $.each(ids || [], function (_, id) {
                if (!$field.length) {
                    $field = $form.find("#" + id).first();
                }
            });

            return $field;
        }

        function setFieldValue($field, value) {
            if (!$field.length) {
                return;
            }

            if ($field.is("select") && !$field.find('option[value="' + String(value).replace(/"/g, '\\"') + '"]').length) {
                $("<option>").val(value).text(value).appendTo($field);
            }

            $field.val(value);
        }

        function showFeedback(message) {
            $("#editor-feedback-message").text(message);

            if (feedbackToast) {
                feedbackToast.show();
                return;
            }

            $(toastElement).addClass("show");
            window.setTimeout(function () {
                $(toastElement).removeClass("show");
            }, 3200);
        }

        function showModal(instance, element) {
            if (instance) {
                instance.show();
            } else if (element) {
                $(element).addClass("show").css("display", "block").attr("aria-hidden", "false");
            }
        }

        function hideModal(instance, element) {
            if (instance) {
                instance.hide();
            } else if (element) {
                $(element).removeClass("show").css("display", "none").attr("aria-hidden", "true");
            }
        }

        function statusDetails(statusValue) {
            const status = String(statusValue || "pendiente").toLowerCase();

            if (status === "completo") {
                return {
                    value: "completo",
                    text: "Completo",
                    cardClass: "is-complete",
                    currentClass: "is-complete",
                    icon: "fa-solid fa-circle-check"
                };
            }

            if (status === "en-edicion") {
                return {
                    value: "en-edicion",
                    text: "En edición",
                    cardClass: "is-editing",
                    currentClass: "is-editing",
                    icon: "fa-solid fa-pen"
                };
            }

            return {
                value: "pendiente",
                text: "Pendiente",
                cardClass: "is-pending",
                currentClass: "is-pending",
                icon: "fa-regular fa-clock"
            };
        }

        function normalizeModuleLabel(label, moduleNumber) {
            const cleanLabel = $.trim(String(label || ""));

            if (/^m[oó]dulo\s+\d+/i.test(cleanLabel)) {
                return cleanLabel.replace(/^m[oó]dulo\s+\d+/i, "Módulo " + moduleNumber);
            }

            return "Módulo " + moduleNumber + (cleanLabel ? " · " + cleanLabel : "");
        }

        function updateStatusAppearance($card, statusValue) {
            const status = statusDetails(statusValue);

            $card
                .removeClass("is-complete is-editing is-pending")
                .addClass(status.cardClass);
            $card.find(".editor-module-state").html(
                '<i class="' + status.icon + '" aria-hidden="true"></i> ' + status.text
            );
        }

        function moduleFields() {
            const $form = $("#module-details-form");

            return {
                title: findField($form, ["module_title"], ["module-title-field"]),
                label: findField($form, ["module_label"], ["module-label-field"]),
                description: findField($form, ["module_description"], ["module-description-field"]),
                status: findField($form, ["module_status"], ["module-status-field"]),
                icon: findField($form, ["module_icon"], ["module-icon-field"]),
                type: findField($form, ["module_type"], ["module-type-field"])
            };
        }

        function reflectModule($select) {
            if (!$select.length) {
                return;
            }

            const title = readData($select, "module-title");
            const label = readData($select, "module-label");
            const description = readData($select, "module-description");
            const statusValue = readData($select, "module-status") || "pendiente";
            const icon = sanitizeIcon(readData($select, "module-icon"), "fa-book-open");
            const status = statusDetails(statusValue);
            const fields = moduleFields();
            const isQuiz = $select.closest(".editor-module-card").hasClass("is-quiz-module");

            $("#editor-current-title").text(title);
            $("#editor-current-label").text(label);
            $("#editor-current-description").text(description);
            $("#editor-current-icon i").attr("class", "fa-solid " + icon);
            $("#editor-current-status")
                .attr("class", "editor-current-status " + status.currentClass)
                .html('<i class="' + status.icon + '" aria-hidden="true"></i> ' + status.text);

            setFieldValue(fields.title, title);
            setFieldValue(fields.label, label);
            setFieldValue(fields.description, description);
            setFieldValue(fields.status, status.value);
            setFieldValue(fields.icon, icon);
            setFieldValue(fields.type, isQuiz ? "quiz" : "content");

            const $preview = $(".editor-heading-preview .module-content-heading");
            $preview.find(".content-type-label").empty()
                .append($("<i>", { "class": "fa-solid " + icon, "aria-hidden": "true" }))
                .append(document.createTextNode(" " + label));
            $preview.find("h2").text(title);
            $preview.find("p").text(description);
        }

        function selectModule($select) {
            if (!$select.length) {
                return;
            }

            const $card = $select.closest(".editor-module-card");
            $(".editor-module-card").removeClass("is-active");
            $(".editor-module-select").removeAttr("aria-current");
            $card.addClass("is-active");
            $select.attr("aria-current", "true");
            switchModuleContent($card);
            reflectModule($select);
        }

        function updateModuleCount() {
            const total = $("#editor-module-list .editor-module-card").length;
            $(".editor-count").text(total).attr("aria-label", total + (total === 1 ? " módulo" : " módulos"));
            $(".course-editor-hero-meta span").first().html(
                '<i class="fa-solid fa-layer-group" aria-hidden="true"></i> ' + total + (total === 1 ? " módulo" : " módulos")
            );
        }

        function renumberModules() {
            $("#editor-module-list .editor-module-card").each(function (index) {
                const moduleNumber = index + 1;
                const $card = $(this);
                const $select = $card.find(".editor-module-select").first();
                const title = readData($select, "module-title") || $card.find("strong").first().text();
                const label = normalizeModuleLabel(readData($select, "module-label"), moduleNumber);

                ensureModuleKey($card);
                $card.attr("data-module-index", index);
                $select
                    .attr("data-module-index", index)
                    .attr("data-module-label", label)
                    .attr("aria-label", "Editar módulo " + moduleNumber + ", " + title);
                $card.find(".editor-module-number").first().text(moduleNumber);
                $card.find(".editor-module-copy small").first().text("Módulo " + moduleNumber);
                $card.find(".editor-module-actions").attr("aria-label", "Acciones del módulo " + moduleNumber);
                $card.find(".editor-module-action")
                    .attr("data-module-index", index)
                    .attr("data-module-title", title);
            });

            updateModuleCount();
        }

        function moduleFormFields() {
            const $form = $("#module-form");

            return {
                title: findField($form, ["module_title"], ["new-module-title"]),
                label: findField($form, ["module_label"], ["new-module-label"]),
                description: findField($form, ["module_description"], ["new-module-description"]),
                status: findField($form, ["module_status"], ["new-module-status"]),
                icon: findField($form, ["module_icon"], ["new-module-icon"]),
                type: findField($form, ["module_type"], ["new-module-type"])
            };
        }

        function resetModuleModal() {
            const form = document.getElementById("module-form");

            moduleFormMode = "add";
            $moduleBeingEdited = $();
            if (form) {
                form.reset();
                $(form).removeClass("was-validated");
            }
            $("#module-form-modal-title").text("Agregar módulo");
            $("#module-form button[type='submit']").html('<i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar módulo');
        }

        function prepareModuleEdit($card) {
            const $select = $card.find(".editor-module-select").first();
            const fields = moduleFormFields();

            moduleFormMode = "edit";
            $moduleBeingEdited = $card;
            setFieldValue(fields.title, readData($select, "module-title"));
            setFieldValue(fields.label, readData($select, "module-label"));
            setFieldValue(fields.description, readData($select, "module-description"));
            setFieldValue(fields.status, readData($select, "module-status"));
            setFieldValue(fields.icon, sanitizeIcon(readData($select, "module-icon")));
            setFieldValue(fields.type, $card.hasClass("is-quiz-module") ? "quiz" : "content");
            $("#module-form").removeClass("was-validated");
            $("#module-form-modal-title").text("Editar módulo");
            $("#module-form button[type='submit']").html('<i class="fa-solid fa-check" aria-hidden="true"></i> Guardar cambios');
        }

        function createModuleCard(values, moduleNumber) {
            const status = statusDetails(values.status);
            const title = escapeHtml(values.title);
            const label = escapeHtml(normalizeModuleLabel(values.label, moduleNumber));
            const description = escapeHtml(values.description);
            const icon = sanitizeIcon(values.icon);
            const isQuizClass = values.type === "quiz" ? " is-quiz-module" : "";
            const stateIcon = '<i class="' + status.icon + '" aria-hidden="true"></i> ';

            return '<li class="editor-module-card ' + status.cardClass + isQuizClass + '" data-module-index="' + (moduleNumber - 1) + '">' +
                '<button class="editor-module-select" type="button" data-module-index="' + (moduleNumber - 1) + '" data-module-title="' + title + '" data-module-label="' + label + '" data-module-description="' + description + '" data-module-status="' + status.value + '" data-module-icon="' + icon + '" aria-label="Editar módulo ' + moduleNumber + ', ' + title + '">' +
                    '<span class="editor-module-number">' + moduleNumber + '</span>' +
                    '<span class="editor-module-copy"><small>Módulo ' + moduleNumber + '</small><strong>' + title + '</strong><span class="editor-module-state">' + stateIcon + status.text + '</span></span>' +
                '</button>' +
                '<div class="editor-module-actions" aria-label="Acciones del módulo ' + moduleNumber + '">' +
                    '<button class="editor-module-action" type="button" data-action="edit" data-module-index="' + (moduleNumber - 1) + '" data-module-title="' + title + '" aria-label="Editar datos del módulo ' + title + '"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>' +
                    '<button class="editor-module-action" type="button" data-action="duplicate" data-module-index="' + (moduleNumber - 1) + '" data-module-title="' + title + '" aria-label="Duplicar módulo ' + title + '"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>' +
                    '<button class="editor-module-action is-danger" type="button" data-action="delete" data-module-index="' + (moduleNumber - 1) + '" data-module-title="' + title + '" aria-label="Eliminar módulo ' + title + '"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>' +
                '</div>' +
            '</li>';
        }

        function validateForm(form) {
            if (!form) {
                return false;
            }

            if (!form.checkValidity()) {
                $(form).addClass("was-validated");
                const invalidField = form.querySelector(":invalid");
                if (invalidField) {
                    invalidField.focus();
                }
                return false;
            }

            $(form).removeClass("was-validated");
            return true;
        }

        function duplicateModule($card) {
            const $source = $card.find(".editor-module-select").first();
            const sourceKey = ensureModuleKey($card);
            const moduleNumber = $("#editor-module-list .editor-module-card").length + 1;
            const values = {
                title: readData($source, "module-title") + " (copia)",
                label: readData($source, "module-label").replace(/^m[oó]dulo\s+\d+\s*·?\s*/i, "") + " copia",
                description: readData($source, "module-description"),
                status: "en-edicion",
                icon: readData($source, "module-icon"),
                type: $card.hasClass("is-quiz-module") ? "quiz" : "content"
            };

            const $copy = $(createModuleCard(values, moduleNumber));
            $("#editor-module-list").append($copy);
            renumberModules();
            const copyKey = ensureModuleKey($copy);
            const $sourceContent = sourceKey === currentModuleKey
                ? $(".editor-workspace > .editor-tab-content").first()
                : moduleContentStates.get(sourceKey);
            moduleContentStates.set(
                copyKey,
                cloneContentState($sourceContent && $sourceContent.length ? $sourceContent : $moduleContentTemplate)
            );
            selectModule($copy.find(".editor-module-select"));
            showFeedback("Módulo duplicado de forma simulada.");
        }

        function openDeleteConfirmation($target, itemName, itemType) {
            if (!$target.length) {
                return;
            }

            pendingDeletion = {
                $target: $target,
                name: itemName || "Elemento",
                type: itemType || "element"
            };
            $("#delete-editor-item-name").text(pendingDeletion.name);
            showModal(deleteModal, deleteModalElement);
        }

        function introBlockMarkup(type, label) {
            const safeLabel = escapeHtml(label);
            const blockId = nextDynamicId("intro-block");
            let preview = "";

            if (type === "video") {
                preview = '<div class="simulated-video" aria-label="Vista previa de video simulado"><span class="video-pattern" aria-hidden="true"></span><span class="video-play-icon"><i class="fa-solid fa-play" aria-hidden="true"></i></span><span class="video-copy"><strong>Nuevo video de capacitación</strong><small>Video simulado · Duración por definir</small></span><span class="video-duration">00:00</span></div>';
            } else if (type === "practice-grid") {
                preview = '<div class="practice-grid"><article><span><i class="fa-solid fa-eye" aria-hidden="true"></i></span><h3>Práctica 1</h3><p>Describe esta práctica.</p></article><article><span><i class="fa-solid fa-hand" aria-hidden="true"></i></span><h3>Práctica 2</h3><p>Describe esta práctica.</p></article></div>';
            } else if (type === "procedure-steps") {
                preview = '<ol class="procedure-steps"><li><span>01</span><div><h3>Paso 1</h3><p>Describe el primer paso del procedimiento.</p></div></li><li><span>02</span><div><h3>Paso 2</h3><p>Describe el segundo paso del procedimiento.</p></div></li></ol>';
            } else {
                preview = '<section class="written-material" aria-labelledby="' + blockId + '-title"><div class="material-heading"><span class="material-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span><div><span>Material escrito</span><h3 id="' + blockId + '-title">Nuevo párrafo introductorio</h3></div></div><p>Escribe aquí el contenido que verá el usuario al iniciar el módulo.</p></section>';
            }

            return '<article class="editor-content-block" data-content-type="' + escapeHtml(type) + '">' +
                '<div class="editor-content-block-toolbar"><span><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i> ' + safeLabel + '</span><div><button type="button" aria-label="Editar ' + safeLabel + '"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="' + safeLabel + '" aria-label="Eliminar ' + safeLabel + '"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></div>' +
                preview +
            '</article>';
        }

        function sectionFormFields() {
            const $form = $("#section-form");

            return {
                title: findField($form, ["section_title"], ["new-section-title"]),
                type: findField($form, ["section_type"], ["new-section-type"]),
                label: findField($form, ["section_label"], ["new-section-label"]),
                icon: findField($form, ["section_icon"], ["new-section-icon"]),
                content: findField($form, ["section_content"], ["new-section-content"])
            };
        }

        function sanitizeColor(value) {
            const color = String(value || "cyan").toLowerCase();
            return ["cyan", "purple", "orange", "pink"].indexOf(color) >= 0 ? color : "cyan";
        }

        function collectSectionConfiguration(type) {
            const $panel = $('.section-config-panel[data-section-config="' + type + '"]');
            const configuration = {};

            if (!$panel.length) {
                return configuration;
            }

            if (type === "unordered-list") {
                configuration.items = $panel.find(".section-list-item").map(function () {
                    return {
                        icon: sanitizeIcon($(this).find("select").first().val(), "fa-check"),
                        text: $.trim($(this).find('input[type="text"]').first().val())
                    };
                }).get().filter(function (item) {
                    return item.text !== "";
                });
            } else if (type === "activity-feedback") {
                const optionValues = $panel.find(".section-answer-option").map(function () {
                    return $.trim($(this).find('input[type="text"]').first().val());
                }).get();
                configuration.options = [optionValues[0] || "", optionValues[1] || "", optionValues[2] || ""];
                configuration.correct = $panel.find('input[name="section-correct-option"]:checked').val() || "1";
            } else if (type === "learning-checklist") {
                configuration.items = $panel.find(".section-checklist-row").map(function () {
                    return {
                        text: $.trim($(this).find(".section-checklist-text").val()),
                        validity: $(this).find(".section-checklist-validity").val() || "correct"
                    };
                }).get().filter(function (item) {
                    return item.text !== "";
                });
            } else if (type === "written-dynamic") {
                configuration.placeholder = $.trim($("#section-reflection-placeholder").val());
            } else if (type === "true-false") {
                configuration.correct = $panel.find('input[name="section-true-false-correct"]:checked').val() || "true";
            } else if (type === "knowledge-review") {
                configuration.cards = $panel.find(".section-knowledge-row").map(function () {
                    return {
                        icon: sanitizeIcon($(this).find(".section-knowledge-icon").val(), "fa-lightbulb"),
                        question: $.trim($(this).find(".section-knowledge-question").val()),
                        answer: $.trim($(this).find(".section-knowledge-answer").val())
                    };
                }).get().filter(function (card) {
                    return card.question !== "" || card.answer !== "";
                });
            } else if (type === "procedure-steps") {
                const stepCount = Number($("#section-step-count").val()) || 2;
                configuration.steps = $panel.find(".section-step-row").filter(function () {
                    return Number($(this).attr("data-step-number")) <= stepCount;
                }).map(function () {
                    return $.trim($(this).find(".section-step-text").val());
                }).get();
            } else if (type === "practice-grid") {
                const practiceCount = Number($("#section-practice-count").val()) || 2;
                configuration.practices = $panel.find(".section-practice-row").filter(function () {
                    return Number($(this).attr("data-practice-number")) <= practiceCount;
                }).map(function () {
                    return {
                        icon: sanitizeIcon($(this).find(".section-practice-icon").val(), "fa-eye"),
                        title: $.trim($(this).find(".section-practice-title").val()),
                        text: $.trim($(this).find(".section-practice-text").val()),
                        color: sanitizeColor($(this).find(".section-practice-color").val())
                    };
                }).get();
            }

            return configuration;
        }

        function sectionPreviewMarkup(type, title, content, id, configuration) {
            const config = configuration || {};
            const safeTitle = escapeHtml(title);
            const safeContent = escapeHtml(content || "Contenido de ejemplo pendiente de personalizar.");

            if (type === "activity-feedback") {
                const options = config.options && config.options.length ? config.options : ["Opción A", "Opción B", "Opción C"];
                const correctOption = String(config.correct || "1");
                let optionsMarkup = "";

                $.each(options, function (index, option) {
                    const isCorrect = String(index + 1) === correctOption;
                    const optionLetter = String.fromCharCode(65 + index);
                    const optionText = option || "Opción " + optionLetter + " por definir";
                    optionsMarkup += '<button class="quiz-option' + (isCorrect ? " is-correct" : "") + '" type="button" data-correct="' + isCorrect + '">' + escapeHtml(optionText) + '</button>';
                });

                return '<section class="activity-card quick-quiz"><div class="activity-heading"><span class="activity-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span><div><span class="activity-status">Pregunta rápida</span><h3>' + safeTitle + '</h3></div></div><p>' + safeContent + '</p><div class="quiz-options">' + optionsMarkup + '</div><p class="activity-feedback">La respuesta correcta queda resaltada en la vista administrativa.</p></section>';
            }

            if (type === "true-false") {
                const correctValue = String(config.correct || "true");
                return '<section class="activity-card quick-quiz"><div class="activity-heading"><span class="activity-icon"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></span><div><span class="activity-status">Verdadero o falso</span><h3>' + safeTitle + '</h3></div></div><p>' + safeContent + '</p><div class="quiz-options"><button class="quiz-option' + (correctValue === "true" ? " is-correct" : "") + '" type="button">Verdadero</button><button class="quiz-option' + (correctValue === "false" ? " is-correct" : "") + '" type="button">Falso</button></div></section>';
            }

            if (type === "learning-checklist") {
                const checklistItems = config.items && config.items.length ? config.items : [
                    { text: "Elemento correcto de ejemplo", validity: "correct" },
                    { text: "Elemento incorrecto de ejemplo", validity: "incorrect" }
                ];
                let checklistMarkup = "";

                $.each(checklistItems, function (_, item) {
                    const isCorrect = item.validity === "correct";
                    checklistMarkup += '<label><input type="checkbox"' + (isCorrect ? " checked" : "") + ' disabled><span>' + escapeHtml(item.text) + '</span><small>' + (isCorrect ? "Respuesta correcta" : "Respuesta incorrecta") + '</small></label>';
                });

                return '<section class="activity-card checklist-activity"><div class="activity-heading"><span class="activity-icon activity-icon-success"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span><div><span class="activity-status">Checklist interactivo</span><h3>' + safeTitle + '</h3></div></div><p>' + safeContent + '</p><div class="learning-checklist">' + checklistMarkup + '</div></section>';
            }

            if (type === "written-dynamic") {
                const placeholder = escapeHtml(config.placeholder || "Escribe una respuesta breve.");
                return '<section class="activity-card reflection-activity"><div class="activity-heading"><span class="activity-icon"><i class="fa-solid fa-pen" aria-hidden="true"></i></span><div><span class="activity-status">Dinámica escrita</span><h3>' + safeTitle + '</h3></div></div><p>' + safeContent + '</p><textarea class="form-control" rows="3" placeholder="' + placeholder + '" disabled></textarea></section>';
            }

            if (type === "knowledge-review") {
                const cards = config.cards && config.cards.length ? config.cards : [
                    { icon: "fa-lightbulb", question: content || "Pregunta de ejemplo", answer: "Respuesta de ejemplo." }
                ];
                let cardsMarkup = "";

                $.each(cards, function (_, card) {
                    cardsMarkup += '<article><span><i class="fa-solid ' + sanitizeIcon(card.icon, "fa-lightbulb") + '" aria-hidden="true"></i></span><small>Pregunta</small><h4>' + escapeHtml(card.question) + '</h4><p>' + escapeHtml(card.answer) + '</p></article>';
                });

                return '<section class="knowledge-review"><div class="knowledge-review-heading"><div><span>Repaso interactivo</span><h3>' + safeTitle + '</h3></div><i class="fa-solid fa-rotate" aria-hidden="true"></i></div><div class="editor-knowledge-preview">' + cardsMarkup + '</div></section>';
            }

            if (type === "procedure-steps") {
                const steps = config.steps && config.steps.length ? config.steps : [content || "Descripción del primer paso.", "Descripción del siguiente paso."];
                let stepsMarkup = "";

                $.each(steps, function (index, step) {
                    const number = String(index + 1).padStart(2, "0");
                    stepsMarkup += '<li><span>' + number + '</span><div><h3>Paso ' + (index + 1) + '</h3><p>' + escapeHtml(step) + '</p></div></li>';
                });

                return '<ol class="procedure-steps">' + stepsMarkup + '</ol>';
            }

            if (type === "practice-grid") {
                const practices = config.practices && config.practices.length ? config.practices : [
                    { icon: "fa-eye", title: title || "Práctica 1", text: content || "Descripción por completar.", color: "cyan" },
                    { icon: "fa-check", title: "Práctica 2", text: "Descripción por completar.", color: "purple" }
                ];
                let practicesMarkup = "";

                $.each(practices, function (_, practice) {
                    const color = sanitizeColor(practice.color);
                    practicesMarkup += '<article class="practice-accent-' + color + '" data-accent-color="' + color + '"><span><i class="fa-solid ' + sanitizeIcon(practice.icon, "fa-eye") + '" aria-hidden="true"></i></span><h3>' + escapeHtml(practice.title) + '</h3><p>' + escapeHtml(practice.text) + '</p></article>';
                });

                return '<div class="practice-grid">' + practicesMarkup + '</div>';
            }

            let listMarkup = "";
            if (type === "unordered-list") {
                const items = config.items && config.items.length ? config.items : [
                    { icon: "fa-check", text: "Primer elemento de ejemplo." },
                    { icon: "fa-check", text: "Segundo elemento de ejemplo." }
                ];

                listMarkup = '<ul class="learning-points">';
                $.each(items, function (_, item) {
                    listMarkup += '<li><i class="fa-solid ' + sanitizeIcon(item.icon, "fa-check") + '" aria-hidden="true"></i> ' + escapeHtml(item.text) + '</li>';
                });
                listMarkup += '</ul>';
            }

            return '<section class="written-material" aria-labelledby="' + id + '-preview-title"><div class="material-heading"><span class="material-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span><div><span>Material escrito</span><h3 id="' + id + '-preview-title">' + safeTitle + '</h3></div></div><p>' + safeContent + '</p>' + listMarkup + '</section>';
        }

        function sectionMarkup(values) {
            const sectionId = nextDynamicId("editor-section");
            const headingId = sectionId + "-heading";
            const title = escapeHtml(values.title);
            const label = escapeHtml(values.label);
            const icon = sanitizeIcon(values.icon, "fa-book-open");
            const preview = sectionPreviewMarkup(values.type, values.title, values.content, sectionId, values.configuration);

            return '<article class="accordion-item editor-section-item">' +
                '<h4 class="accordion-header" id="' + headingId + '"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#' + sectionId + '" aria-expanded="true" aria-controls="' + sectionId + '"><span class="editor-section-icon"><i class="fa-solid ' + icon + '" aria-hidden="true"></i></span><span><small>' + label + '</small><strong>' + title + '</strong></span><span class="editor-section-badge is-editing">En edición</span></button></h4>' +
                '<div id="' + sectionId + '" class="accordion-collapse collapse show" aria-labelledby="' + headingId + '"><div class="accordion-body"><div class="editor-item-actions"><button type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><button type="button"><i class="fa-regular fa-copy" aria-hidden="true"></i> Duplicar</button><button type="button" data-editor-delete="' + title + '"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button></div>' + preview + '</div></div>' +
            '</article>';
        }

        function resourceFormFields() {
            const $form = $("#resource-form");

            return {
                title: findField($form, ["resource_title", "resource_name"], ["new-resource-name"]),
                description: findField($form, ["resource_description"], ["new-resource-description"]),
                type: findField($form, ["resource_type"], ["new-resource-type"]),
                icon: findField($form, ["resource_icon"], ["new-resource-icon"]),
                reference: findField($form, ["resource_reference"], ["new-resource-reference"])
            };
        }

        function resourceMarkup(values) {
            const title = escapeHtml(values.title);
            const description = escapeHtml(values.description);
            const type = escapeHtml(values.typeLabel || "Recurso");
            const reference = escapeHtml(values.reference || "Referencia simulada sin definir");
            const icon = sanitizeIcon(values.icon, "fa-file-lines");
            const colorClasses = ["resource-icon-cyan", "resource-icon-purple", "resource-icon-orange"];
            const colorClass = colorClasses[$("#editor-resource-list .editor-resource-item").length % colorClasses.length];

            return '<article class="editor-resource-item" data-resource-reference="' + reference + '" title="Referencia simulada: ' + reference + '"><div class="resource-card"><span class="resource-icon ' + colorClass + '"><i class="fa-solid ' + icon + '" aria-hidden="true"></i></span><span><strong>' + title + '</strong><small>' + type + ' · ' + description + '</small></span><span class="resource-link-icon"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span></div><div class="editor-resource-reference"><i class="fa-solid fa-link" aria-hidden="true"></i><span>' + reference + '</span></div><div class="editor-resource-actions"><button type="button" aria-label="Editar ' + title + '"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="' + title + '" aria-label="Eliminar ' + title + '"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></article>';
        }

        function questionMarkup(questionNumber) {
            const questionId = nextDynamicId("question-editor");
            const headingId = questionId + "-heading";
            const radioName = questionId + "-correct";

            return '<article class="accordion-item editor-question-item" data-question-number="' + questionNumber + '">' +
                '<h4 class="accordion-header" id="' + headingId + '"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#' + questionId + '" aria-expanded="true" aria-controls="' + questionId + '"><span class="editor-question-number">' + questionNumber + '</span><span><small>Pregunta ' + questionNumber + '</small><strong>Nueva pregunta de repaso</strong></span><span class="editor-question-points">1 punto</span></button></h4>' +
                '<div id="' + questionId + '" class="accordion-collapse collapse show" aria-labelledby="' + headingId + '"><div class="accordion-body">' +
                    '<div class="editor-field"><label for="' + questionId + '-text">Texto de la pregunta</label><textarea id="' + questionId + '-text" class="form-control" rows="2">Nueva pregunta de repaso</textarea></div>' +
                    '<fieldset class="quiz-option-editor"><legend>Opciones y respuesta correcta</legend>' +
                        '<div class="quiz-option-row"><label for="' + questionId + '-option-1"><input id="' + questionId + '-option-1" type="radio" name="' + radioName + '" value="1" checked><span>A</span></label><input id="' + questionId + '-option-text-1" class="form-control" type="text" value="Opción A" aria-label="Texto de la opción A"></div>' +
                        '<div class="quiz-option-row"><label for="' + questionId + '-option-2"><input id="' + questionId + '-option-2" type="radio" name="' + radioName + '" value="2"><span>B</span></label><input id="' + questionId + '-option-text-2" class="form-control" type="text" value="Opción B" aria-label="Texto de la opción B"></div>' +
                        '<div class="quiz-option-row"><label for="' + questionId + '-option-3"><input id="' + questionId + '-option-3" type="radio" name="' + radioName + '" value="3"><span>C</span></label><input id="' + questionId + '-option-text-3" class="form-control" type="text" value="Opción C" aria-label="Texto de la opción C"></div>' +
                    '</fieldset>' +
                    '<div class="editor-question-footer"><label for="' + questionId + '-points">Puntuación <input id="' + questionId + '-points" class="form-control" type="number" min="0" value="1"></label><button type="button" data-editor-delete="Pregunta ' + questionNumber + '"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar pregunta</button></div>' +
                '</div></div>' +
            '</article>';
        }

        function renumberQuestions() {
            $("#editor-question-list .editor-question-item").each(function (index) {
                const number = index + 1;
                const $question = $(this);
                $question.attr("data-question-number", number);
                $question.find(".editor-question-number").first().text(number);
                $question.find(".accordion-button small").first().text("Pregunta " + number);
                $question.find("[data-editor-delete]").attr("data-editor-delete", "Pregunta " + number);
            });
        }

        function refreshElementIds($scope, prefix) {
            const replacements = [];

            $scope.find("[id]").addBack("[id]").each(function () {
                const oldId = this.id;
                const newId = nextDynamicId(prefix || "editor-copy");
                replacements.push({ oldId: oldId, newId: newId });
                this.id = newId;
            });

            $scope.find("*").addBack().each(function () {
                const $element = $(this);

                $.each(["for", "aria-labelledby", "aria-controls", "data-bs-target"], function (_, attribute) {
                    let value = $element.attr(attribute);
                    if (!value) {
                        return;
                    }

                    $.each(replacements, function (_, replacement) {
                        value = value.split(replacement.oldId).join(replacement.newId);
                    });
                    $element.attr(attribute, value);
                });
            });
        }

        function duplicateSection($section) {
            const $copy = cloneContentState($section);

            refreshElementIds($copy, "section-copy");
            $copy.find("[contenteditable]").removeAttr("contenteditable");
            $copy.removeClass("is-inline-editing");
            $copy.find(".accordion-button").first().addClass("collapsed").attr("aria-expanded", "false");
            $copy.find(".accordion-collapse").first().removeClass("show");
            $section.after($copy);
            showFeedback("Sección duplicada de forma simulada.");
        }

        function toggleInlineEditing($container, $targets, itemLabel) {
            const enableEditing = !$container.hasClass("is-inline-editing");
            const $checklistInputs = $container.find('.learning-checklist input[type="checkbox"]');

            $container.toggleClass("is-inline-editing", enableEditing);
            if (enableEditing) {
                $targets.attr("contenteditable", "true");
                $checklistInputs.prop("disabled", false);
                $targets.first().trigger("focus");
            } else {
                $targets.removeAttr("contenteditable");
                $checklistInputs.prop("disabled", true);
            }

            showFeedback(
                enableEditing
                    ? "Edición visual activada para " + itemLabel + "."
                    : "Edición visual finalizada para " + itemLabel + "."
            );
        }

        function updateCountedConfigurationRows() {
            const stepCount = Number($("#section-step-count").val()) || 2;
            const practiceCount = Number($("#section-practice-count").val()) || 2;

            $('.section-step-row[data-step-number]').each(function () {
                $(this).prop("hidden", Number($(this).attr("data-step-number")) > stepCount);
            });
            $('.section-practice-row[data-practice-number]').each(function () {
                $(this).prop("hidden", Number($(this).attr("data-practice-number")) > practiceCount);
            });
        }

        function toggleSectionConfiguration(type) {
            const $panels = $(".section-config-panel");
            const $selectedPanel = $panels.filter('[data-section-config="' + type + '"]');

            $panels.prop("hidden", true);
            $selectedPanel.prop("hidden", false);
            $("#section-type-help").prop("hidden", $selectedPanel.length > 0);
            updateCountedConfigurationRows();
        }

        function resetSectionForm() {
            const form = document.getElementById("section-form");

            if (!form) {
                return;
            }

            $(form).find(".is-dynamic-config-row").remove();
            form.reset();
            $(form).removeClass("was-validated");
            toggleSectionConfiguration("");
        }

        function addSectionConfigurationRow($button) {
            const type = readData($button, "config-type");
            const rowSelector = type === "knowledge" ? ".section-knowledge-row" : ".section-checklist-row";
            const $row = $button.siblings(rowSelector).last();

            if (!$row.length) {
                return;
            }

            const $copy = cloneContentState($row).addClass("is-dynamic-config-row");
            refreshElementIds($copy, "section-config");
            $copy.find('input[type="text"], textarea').val("");
            $copy.find("select").each(function () {
                this.selectedIndex = 0;
            });
            $copy.insertBefore($button);
            $copy.find("input, textarea").first().trigger("focus");
        }

        $(document).on("click", ".editor-module-select", function () {
            selectModule($(this));
        });

        $(document).on("click", ".editor-module-action", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const $card = $(this).closest(".editor-module-card");
            const action = readData($(this), "action");

            if (action === "edit") {
                prepareModuleEdit($card);
                showModal(moduleModal, moduleModalElement);
            } else if (action === "duplicate") {
                duplicateModule($card);
            } else if (action === "delete") {
                const moduleName = readData($card.find(".editor-module-select").first(), "module-title");
                openDeleteConfirmation($card, moduleName, "module");
            }
        });

        $("#open-module-modal, #add-editor-module").on("click", function () {
            resetModuleModal();
        });

        $("#module-form").on("submit", function (event) {
            event.preventDefault();
            const form = this;

            if (!validateForm(form)) {
                return;
            }

            const fields = moduleFormFields();
            const values = {
                title: $.trim(fields.title.val()),
                label: $.trim(fields.label.val()),
                description: $.trim(fields.description.val()),
                status: fields.status.val() || "pendiente",
                icon: sanitizeIcon(fields.icon.val()),
                type: fields.type.val() || "content"
            };

            if (moduleFormMode === "edit" && $moduleBeingEdited.length) {
                const moduleNumber = $moduleBeingEdited.index() + 1;
                const normalizedLabel = normalizeModuleLabel(values.label, moduleNumber);
                const $select = $moduleBeingEdited.find(".editor-module-select").first();

                $select
                    .attr("data-module-title", values.title)
                    .attr("data-module-label", normalizedLabel)
                    .attr("data-module-description", values.description)
                    .attr("data-module-status", values.status)
                    .attr("data-module-icon", values.icon);
                $moduleBeingEdited.find(".editor-module-copy strong").first().text(values.title);
                $moduleBeingEdited.toggleClass("is-quiz-module", values.type === "quiz");
                updateStatusAppearance($moduleBeingEdited, values.status);
                renumberModules();
                selectModule($select);
                showFeedback("Datos del módulo actualizados de forma simulada.");
            } else {
                const moduleNumber = $("#editor-module-list .editor-module-card").length + 1;
                const $newCard = $(createModuleCard(values, moduleNumber));
                $("#editor-module-list").append($newCard);
                renumberModules();
                selectModule($newCard.find(".editor-module-select"));
                showFeedback("Módulo agregado de forma simulada.");
            }

            hideModal(moduleModal, moduleModalElement);
            form.reset();
        });

        $(document).on("click", "[data-simulated-action='update-module-data']", function () {
            const $activeCard = $(".editor-module-card.is-active").first();
            const $select = $activeCard.find(".editor-module-select").first();
            const fields = moduleFields();

            if (!$select.length) {
                return;
            }

            const moduleNumber = $activeCard.index() + 1;
            const title = $.trim(fields.title.val());
            const label = normalizeModuleLabel(fields.label.val(), moduleNumber);
            const description = $.trim(fields.description.val());
            const status = fields.status.val() || "pendiente";
            const icon = sanitizeIcon(fields.icon.val());

            if (!title || !description) {
                showFeedback("Completa el título y la descripción del módulo.");
                (!title ? fields.title : fields.description).trigger("focus");
                return;
            }

            $select
                .attr("data-module-title", title)
                .attr("data-module-label", label)
                .attr("data-module-description", description)
                .attr("data-module-status", status)
                .attr("data-module-icon", icon);
            $activeCard.find(".editor-module-copy strong").first().text(title);
            $activeCard.toggleClass("is-quiz-module", fields.type.val() === "quiz");
            updateStatusAppearance($activeCard, status);
            renumberModules();
            reflectModule($select);
            showFeedback("Datos aplicados de forma simulada.");
        });

        $(document).on("submit", "#module-details-form", function (event) {
            event.preventDefault();
            $("[data-simulated-action='update-module-data']").trigger("click");
        });

        $(".editor-tab-button").on("click", function () {
            const selectedTab = readData($(this), "editor-tab");

            $(".editor-tab-button").removeClass("is-active").attr("aria-selected", "false");
            $(this).addClass("is-active").attr("aria-selected", "true");
            $(".editor-tab-panel").removeClass("is-active").prop("hidden", true);
            $('.editor-tab-panel[data-editor-panel="' + selectedTab + '"]').addClass("is-active").prop("hidden", false);
        }).on("keydown", function (event) {
            const $tabs = $(".editor-tab-button");
            const currentIndex = $tabs.index(this);
            let nextIndex = currentIndex;

            if (event.key === "ArrowRight") {
                nextIndex = (currentIndex + 1) % $tabs.length;
            } else if (event.key === "ArrowLeft") {
                nextIndex = (currentIndex - 1 + $tabs.length) % $tabs.length;
            } else if (event.key === "Home") {
                nextIndex = 0;
            } else if (event.key === "End") {
                nextIndex = $tabs.length - 1;
            } else {
                return;
            }

            event.preventDefault();
            $tabs.eq(nextIndex).trigger("click").trigger("focus");
        });

        $(document).on("click", ".intro-insert-action", function () {
            const type = readData($(this), "insert-type") || "paragraph";
            const label = readData($(this), "insert-label") || "Nuevo contenido";

            $("#intro-builder-list").append(introBlockMarkup(type, label));
            showFeedback(label + " agregado de forma simulada.");
        });

        $(document).on("click", "#open-section-modal", function () {
            resetSectionForm();
        });

        $(document).on("change", "#new-section-type", function () {
            toggleSectionConfiguration($(this).val());
        });

        $(document).on("change", "#section-step-count, #section-practice-count", updateCountedConfigurationRows);

        $(document).on("click", ".add-section-config-item", function () {
            addSectionConfigurationRow($(this));
        });

        $("#section-form").on("submit", function (event) {
            event.preventDefault();

            if (!validateForm(this)) {
                return;
            }

            const fields = sectionFormFields();
            const selectedTypeLabel = fields.type.find("option:selected").text();
            const values = {
                title: $.trim(fields.title.val()),
                type: fields.type.val() || "written-material",
                label: $.trim(fields.label.val()) || selectedTypeLabel,
                icon: sanitizeIcon(fields.icon.val()),
                content: $.trim(fields.content.val()),
                configuration: collectSectionConfiguration(fields.type.val())
            };

            $("#editor-section-list .accordion-collapse").removeClass("show");
            $("#editor-section-list .accordion-button").addClass("collapsed").attr("aria-expanded", "false");
            $("#editor-section-list").append(sectionMarkup(values));
            hideModal(sectionModal, sectionModalElement);
            resetSectionForm();
            showFeedback("Sección agregada de forma simulada.");
        });

        $(document).on("click", "#open-resource-modal", function () {
            const form = document.getElementById("resource-form");
            if (form) {
                form.reset();
                $(form).removeClass("was-validated");
            }
        });

        $("#resource-form").on("submit", function (event) {
            event.preventDefault();

            if (!validateForm(this)) {
                return;
            }

            const fields = resourceFormFields();
            const values = {
                title: $.trim(fields.title.val()),
                description: $.trim(fields.description.val()),
                type: fields.type.val(),
                typeLabel: fields.type.find("option:selected").text(),
                icon: sanitizeIcon(fields.icon.val(), "fa-file-lines"),
                reference: $.trim(fields.reference.val())
            };

            $("#editor-resource-list").append(resourceMarkup(values));
            hideModal(resourceModal, resourceModalElement);
            this.reset();
            showFeedback("Recurso agregado de forma simulada.");
        });

        $(document).on("click", "#add-quiz-question", function () {
            const questionNumber = $("#editor-question-list .editor-question-item").length + 1;

            $("#editor-question-list .accordion-collapse").removeClass("show");
            $("#editor-question-list .accordion-button").addClass("collapsed").attr("aria-expanded", "false");
            $("#editor-question-list").append(questionMarkup(questionNumber));
            showFeedback("Pregunta agregada de forma simulada.");
        });

        $(document).on("click", ".editor-content-block-toolbar button:not([data-editor-delete])", function () {
            const $block = $(this).closest(".editor-content-block");
            const $editableElements = $block.children().not(".editor-content-block-toolbar").find("h3, h4, p, strong");
            toggleInlineEditing($block, $editableElements, "el bloque introductorio");
        });

        $(document).on("click", ".editor-item-actions button:not([data-editor-delete])", function () {
            const $button = $(this);
            const $section = $button.closest(".editor-section-item");

            if ($button.find(".fa-copy").length) {
                duplicateSection($section);
                return;
            }

            const $editableElements = $section.find(
                ".accordion-body h3, .accordion-body h4, .accordion-body p, " +
                ".learning-points li, .learning-checklist label > span, " +
                ".learning-checklist label > small, .quiz-option"
            );
            toggleInlineEditing($section, $editableElements, "la sección");
        });

        $(document).on("change", '.editor-section-item.is-inline-editing .learning-checklist input[type="checkbox"]', function () {
            $(this).closest("label").find("small").first().text(
                $(this).prop("checked") ? "Respuesta correcta" : "Respuesta incorrecta"
            );
        });

        $(document).on("click", ".editor-section-item.is-inline-editing .quiz-option", function (event) {
            event.preventDefault();
            const $option = $(this);

            $option.siblings(".quiz-option").removeClass("is-correct").attr("data-correct", "false");
            $option.addClass("is-correct").attr("data-correct", "true");
        });

        $(document).on("click", ".editor-resource-actions button:not([data-editor-delete])", function () {
            const $resource = $(this).closest(".editor-resource-item");
            const $editableElements = $resource.find(".resource-card strong, .resource-card small, .editor-resource-reference span");
            toggleInlineEditing($resource, $editableElements, "el recurso");
        });

        $(document).on("click", "[data-editor-delete]", function (event) {
            event.preventDefault();
            const $button = $(this);
            const $target = $button.closest(".editor-content-block, .editor-section-item, .editor-resource-item, .editor-question-item").first();
            const name = readData($button, "editor-delete") || "Elemento";
            let type = "element";

            if ($target.hasClass("editor-question-item")) {
                type = "question";
            }
            openDeleteConfirmation($target, name, type);
        });

        $("#confirm-editor-item-delete").on("click", function () {
            if (!pendingDeletion || !pendingDeletion.$target.length) {
                hideModal(deleteModal, deleteModalElement);
                return;
            }

            const deletion = pendingDeletion;
            const deletedName = pendingDeletion.name;

            if (deletion.type === "module") {
                const wasActiveModule = deletion.$target.hasClass("is-active");
                const deletedKey = ensureModuleKey(deletion.$target);
                const $nextModule = deletion.$target.next(".editor-module-card").length
                    ? deletion.$target.next(".editor-module-card")
                    : deletion.$target.prev(".editor-module-card");

                moduleContentStates.delete(deletedKey);
                if (wasActiveModule) {
                    detachCourseResourcesPanel();
                    $(".editor-workspace > .editor-tab-content").remove();
                    currentModuleKey = null;
                }

                deletion.$target.remove();
                renumberModules();
                if (wasActiveModule && $nextModule.length) {
                    selectModule($nextModule.find(".editor-module-select").first());
                } else if (!$("#editor-module-list .editor-module-card").length) {
                    showWorkspaceEmpty();
                }
            } else {
                deletion.$target.remove();
            }

            if (deletion.type === "question") {
                renumberQuestions();
            }

            hideModal(deleteModal, deleteModalElement);
            pendingDeletion = null;
            showFeedback(deletedName + " eliminado de la maqueta. El cambio no es permanente.");
        });

        $("#save-course-content").on("click", function () {
            showFeedback("Cambios guardados correctamente de forma simulada.");
        });

        $(moduleModalElement).on("shown.bs.modal", function () {
            moduleFormFields().label.trigger("focus");
        });

        $(sectionModalElement).on("shown.bs.modal", function () {
            sectionFormFields().type.trigger("focus");
        });

        $(resourceModalElement).on("shown.bs.modal", function () {
            resourceFormFields().title.trigger("focus");
        });

        const $initialModule = $(".editor-module-card.is-active .editor-module-select").first().length
            ? $(".editor-module-card.is-active .editor-module-select").first()
            : $(".editor-module-select").first();

        renumberModules();
        toggleSectionConfiguration($("#new-section-type").val() || "");
        if ($initialModule.length) {
            selectModule($initialModule);
        } else {
            showWorkspaceEmpty();
        }
    });
})(jQuery);
