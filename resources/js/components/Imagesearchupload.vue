<template>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Image upload</div>
                <div class="card-content">
                    <form>
                        <input type="file" class="form-control" @onchange="onChange">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
name: "Imagesearch",
    methods: {
    onChange(e) {
        this.file = e.target.files[0];
    }
    }
}
</script>

<style scoped>

</style>
