import { createApp, reactive, shallowRef } from 'vue'

const $app$ = createApp({
    setup() {
        const form = reactive({
            name: '',
            email: '',
            message: '',
        })
        const data = shallowRef(null)

        const handleSubmit = () => {
            console.log(form)
            $.post('/api/contact', form, null, 'json').then((res) => {
                console.log(res)
                data.value = res.data
            })
        }

        return {
            form,
            data,
            handleSubmit,
        }
    },
}).mount('#app')

window.$app$ = $app$
