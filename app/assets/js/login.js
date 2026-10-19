import { createApp, reactive, ref } from 'vue'

const $app$ = createApp({
    setup() {
        const form = reactive({
            name: '123',
            pass: '456',
        })
        const data = ref(null)
        const pending = ref(false)
        const error = ref('')

        const handleSubmit = () => {
            error.value = ''
            pending.value = true
            $.post('/api/contact', form, null, 'json').then((res) => {
                data.value = res.data
            }).fail(() => {
                error.value = '登录失败，请稍后再试。'
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
