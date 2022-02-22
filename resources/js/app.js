require('./bootstrap');

import {createApp, configureStore} from 'vue'
import HelloWorld from './components/Welcome'
import ImageUpload from './components/ImageSearchUpload'

const app = createApp({
    components: {
        'hello-world': HelloWorld,
        'image-upload': ImageUpload
    }
});

/*
app.config.ignoredElements = [
    'msc-lens'
]
 */

app.config.compilerOptions.isCustomElement = (tag) => {
    return tag.startsWith('msc-lens')
}

app.mount('#app')
