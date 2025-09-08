<template>
<div>
<h2>Upload File</h2>
<input type="file" @change="upload" />
</div>
</template>


<script setup>
import api from '../api/http'


async function upload(e) {
const file = e.target.files[0]
const form = new FormData()
form.append('file', file)
await api.post('/import', form)
alert('Uploaded!')
}
</script>