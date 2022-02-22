<template>

    <div class="container">
        <h1 class="fw-light text-center text-lg-start mt-4 mb-0">上傳檔案測試</h1>


        <div class="row">
            <div class="col-3">
                <div class="card">
                    <div class="card-header">Image upload</div>
                    <div class="card-content">
                        <form enctype="multipart/form-data">
                            <input type="file" class="form-control" @change="onFileChange" multiple>
                            <button class="btn btn-primary btn-block"  @click="onFileUpload" >Upload</button>
                        </form>
                    </div>
                </div>
                <div v-if="previewImage">
                    <div>
                        <img class="preview img-thumbnail" :src="previewImage" />
                    </div>
                </div>
            </div>
            <div class="col-8">比對結果
                <div class="row text-center text-lg-start">
                    <div class="col-lg-3 col-md-4 col-6" v-for="(item,index) in this.imageList">
                        <a class="d-block mb-4 h-100" :href="item.product.productURL">
                            <h3>{{ item.product.title}}</h3>
                            <img class="img-fluid img-thumbnail" :src="item.product.mainImageURL" :alt="item.product.title">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data(){
        return{
            newItem: 'Hello world',
            previewImage: undefined,
            imageList: [],
        }
    },


    methods: {
    onFileChange(e) {
        this.file = e.target.files[0];
        this.previewImage = URL.createObjectURL(this.file);
        console.log(" select file");
        console.log(e.target.files);
    },
        onFileUpload(e) {
            e.preventDefault();
            let fd = new FormData();
            const config = {
                /*
                headers: {
                    'content-type': 'multipart/form-data'
                },
                (
                 */
                crossdomain: true,
                async:true,

                crossDomain:true,

            }
            fd.append('image', this.file);
           //axios.post('/uploadImage', fd, config)
            //  axios.post('http://127.0.0.1:5000/img/search', fd, config)

            axios.post('https://imgapi.lalacube.com/img/search', fd, config)
                .then( (response)=> {
                    console.log(response.data);
                    let productlist = [];
                    for (let i in response.data) {
                        console.log(i);
                        console.log("response.data")
                        console.log(response.data[i][3]);
                        let tmpstring = response.data[i][3].replace(/\\/g, "");

                        let tmpjson = JSON.parse(tmpstring);
                        console.log(tmpjson);
                        productlist.push({'product': tmpjson});
                    }
                    this.imageList = productlist;

                })
            .catch( function(error){ console.log(error);});


        }
    }
}
</script>

<style scoped>

</style>
