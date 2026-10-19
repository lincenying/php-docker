import { createApp, reactive, ref, shallowRef } from 'vue'

const $app$ = createApp({
    setup() {
        const form = reactive({
            name: '',
            email: '',
            message: '',
        })
        const data = shallowRef(null)
        const pending = ref(false)
        const error = ref('')

        const handleSubmit = () => {
            error.value = ''
            pending.value = true
            $.post('/api/contact', form, null, 'json').then((res) => {
                data.value = res.data
            }).fail(() => {
                error.value = '发送失败，请稍后再试。'
            }).always(() => {
                pending.value = false
            })
        }

        return {
            form,
            data,
            pending,
            error,
            handleSubmit,
        }
    },
}).mount('#app')

window.$app$ = $app$
