<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import {
    BoldIcon,
    ItalicIcon,
    UnderlineIcon,
    LinkIcon,
    PhotoIcon,
    ListBulletIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Write your content here...' },
    minHeight: { type: String, default: '200px' },
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const imageInputRef = ref(null);
const uploading = ref(false);

function execCommand(cmd, value = null) {
    editorRef.value?.focus();
    document.execCommand(cmd, false, value);
    syncToModel();
}

function syncToModel() {
    if (!editorRef.value) return;
    let html = editorRef.value.innerHTML;
    // Treat effectively empty content as empty string
    const textContent = editorRef.value.textContent?.trim() || '';
    if (!textContent && !html.includes('<img')) {
        html = '';
    }
    if (html !== props.modelValue) {
        emit('update:modelValue', html);
    }
}

function handleInput() {
    syncToModel();
}

function handlePaste(e) {
    // Allow default paste; sync after a tick so content is in the editor
    setTimeout(syncToModel, 0);
}

function handleBlur() {
    // Ensure content is synced when focus leaves the editor
    syncToModel();
}

function addLink() {
    const url = prompt('Enter URL:');
    if (url) execCommand('createLink', url);
}

/** Find the UL that contains the current selection (or the focused node). */
function getContainingList() {
    const sel = window.getSelection();
    if (!sel || sel.rangeCount === 0) return null;
    let node = sel.anchorNode;
    while (node && node !== editorRef.value) {
        if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'UL') return node;
        node = node.parentElement;
    }
    return null;
}

/** Insert a bullet list and apply a list-style class (disc, circle, or square). */
function insertBulletList(listClass) {
    editorRef.value?.focus();
    document.execCommand('insertUnorderedList', false, null);
    const ul = getContainingList();
    if (ul) {
        ul.classList.remove('list-disc', 'list-circle', 'list-square');
        if (listClass) ul.classList.add(listClass);
    }
    syncToModel();
}

function triggerImageUpload() {
    imageInputRef.value?.click();
}

async function onImageSelected(event) {
    const file = event.target.files?.[0];
    if (!file || !file.type.startsWith('image/')) return;
    event.target.value = '';

    uploading.value = true;
    const formData = new FormData();
    formData.append('image', file);

    try {
        const { data } = await window.axios.post('/portal/posts/upload-image', formData, {
            headers: { 'Content-Type': 'multipart/form-data', 'Accept': 'application/json' },
        });
        if (data?.url) {
            execCommand('insertImage', data.url);
        }
    } catch (err) {
        console.error('Image upload failed', err);
        alert('Image upload failed. Please try again.');
    } finally {
        uploading.value = false;
    }
}

function setContent(html) {
    if (!editorRef.value) return;
    const safe = html || '';
    if (editorRef.value.innerHTML !== safe) {
        editorRef.value.innerHTML = safe;
    }
}

watch(() => props.modelValue, (val) => {
    if (editorRef.value) {
        const target = val || '';
        if (editorRef.value.innerHTML !== target) setContent(target);
    }
}, { immediate: true });

onMounted(() => {
    if (editorRef.value && (props.modelValue || '') !== editorRef.value.innerHTML) {
        setContent(props.modelValue || '');
    }
});

onBeforeUnmount(() => {
    syncToModel();
});

defineExpose({ syncToModel });
</script>

<template>
    <div class="wysiwyg-editor rounded-lg border border-slate-300 overflow-hidden focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-0.5 p-1.5 bg-slate-100 border-b border-slate-300">
            <button
                type="button"
                title="Bold"
                class="p-2 rounded hover:bg-slate-200 text-slate-700"
                @click="execCommand('bold')"
            >
                <BoldIcon class="w-4 h-4" />
            </button>
            <button
                type="button"
                title="Italic"
                class="p-2 rounded hover:bg-slate-200 text-slate-700"
                @click="execCommand('italic')"
            >
                <ItalicIcon class="w-4 h-4" />
            </button>
            <button
                type="button"
                title="Underline"
                class="p-2 rounded hover:bg-slate-200 text-slate-700"
                @click="execCommand('underline')"
            >
                <UnderlineIcon class="w-4 h-4" />
            </button>
            <button
                type="button"
                title="Insert link"
                class="p-2 rounded hover:bg-slate-200 text-slate-700"
                @click="addLink"
            >
                <LinkIcon class="w-4 h-4" />
            </button>
            <button
                type="button"
                title="Upload image"
                class="p-2 rounded hover:bg-slate-200 text-slate-700 disabled:opacity-50"
                :disabled="uploading"
                @click="triggerImageUpload"
            >
                <PhotoIcon class="w-4 h-4" />
            </button>
            <span class="w-px h-6 bg-slate-300 mx-1" aria-hidden="true" />
            <button
                type="button"
                title="Bullet list (disc)"
                class="p-2 rounded hover:bg-slate-200 text-slate-700"
                @click="insertBulletList('list-disc')"
            >
                <ListBulletIcon class="w-4 h-4" />
            </button>
            <button
                type="button"
                title="Bullet list (circle)"
                class="p-2 rounded hover:bg-slate-200 text-slate-700 text-xs font-medium"
                @click="insertBulletList('list-circle')"
            >
                ○
            </button>
            <button
                type="button"
                title="Bullet list (square)"
                class="p-2 rounded hover:bg-slate-200 text-slate-700 text-xs font-medium"
                @click="insertBulletList('list-square')"
            >
                ▪
            </button>
            <input
                ref="imageInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onImageSelected"
            />
        </div>
        <!-- Editor -->
        <div
            ref="editorRef"
            contenteditable="true"
            class="w-full px-4 py-3 bg-white text-slate-800 prose prose-slate max-w-none focus:outline-none"
            :style="{ minHeight }"
            :data-placeholder="placeholder"
            @input="handleInput"
            @paste="handlePaste"
            @blur="handleBlur"
        />
    </div>
</template>

<style scoped>
.wysiwyg-editor [contenteditable]:empty::before {
    content: attr(data-placeholder);
    color: #94a3b8;
}
.wysiwyg-editor :deep(.prose) {
    margin: 0;
}
.wysiwyg-editor :deep(.prose p) {
    margin: 0 0 0.75em;
}
.wysiwyg-editor :deep(.prose p:last-child) {
    margin-bottom: 0;
}
.wysiwyg-editor :deep(.prose img) {
    max-width: 100%;
    height: auto;
}
.wysiwyg-editor :deep(.prose a) {
    color: #2563eb;
    text-decoration: underline;
}
.wysiwyg-editor :deep(.prose ul) {
    margin: 0.5em 0 0.75em 1.5em;
    padding-left: 0.25em;
}
.wysiwyg-editor :deep(.prose ul.list-disc) {
    list-style-type: disc;
}
.wysiwyg-editor :deep(.prose ul.list-circle) {
    list-style-type: circle;
}
.wysiwyg-editor :deep(.prose ul.list-square) {
    list-style-type: square;
}
.wysiwyg-editor :deep(.prose li) {
    margin: 0.25em 0;
}
</style>
