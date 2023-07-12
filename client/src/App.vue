<script setup>
import {onMounted, ref} from "vue"
import ButtonComponent from "@/components/buttonComponent.vue"
import axios from "axios";

onMounted(() => document.body.classList.add('bg-gray-800'))

const productCode = ref([
  {
    value: ''
  }
]);

const accountId = ref('');

const result = ref('');

async function getPrice() {

  // clean result
  result.value = '';

  // mount the endpoint
  let url = "http://127.0.0.1:8000/api/product-price?"
  productCode.value.forEach(e => url += 'prod_code[]=' + e.value + "&");
  console.log(accountId.value);
  if (accountId.value !== '') url += 'account_id=' + accountId.value;

  // request
  await axios.get(url)
      .then(function (res) {
        // success
        result.value = res.data.result + '\n';
        res.data.products_prices.forEach(e => result.value += JSON.stringify(e, undefined, 2) + '\n');

      })
      .catch(function (error) {
        // error
        if (error.request.status === 404) {
          result.value += error.response.data.result;
        } else {
          result.value += 'Error processing the search, sorry!';
        }

      })

}

</script>

<template>
  <header>
    <div class="flex">

    </div>
  </header>

  <main>

    <form @submit.prevent="getPrice">
      <div class="flex flex-col w-1/5 mx-auto mt-5">
        <h1 class="text-3xl font-bold text-red-500 my-auto mb-3">Search Prices</h1>

        <div v-for="(find, n) in productCode" :key="n">
          <input
              class="pl-3 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 placeholder:text-gray-400 sm:text-sm sm:leading-6 focus:ring-0 focus:ring-offset-0"
              placeholder="Product Code" v-model="find.value" name="sku[]"
              type="text" required="required"/>
          <p class="mt-0 mb-2 text-sm text-gray-500">Example product code: VRJPOO</p>
        </div>

        <input
            class="pl-3 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 placeholder:text-gray-400 sm:text-sm sm:leading-6 focus:ring-0 focus:ring-offset-0"
            placeholder="Account Id" v-model="accountId" name="account_id" type="text" :required="false"/>
        <p class="mt-0 mb-2 text-sm text-gray-500">Example Account id: 461</p>

        <button-component type="submit" color="#005b96" custom-class="mx-auto">
          Search Price
        </button-component>

      </div>
      <div class="flex w-1/5 mx-auto mt-5">
        <button-component @click="productCode.length > 1 ? productCode.pop() : productCode" type="button"
                          class="mx-auto"
                          custom-class="bg-red-500 hover:bg-red-700"> - Product
        </button-component>
        <button-component @click="productCode.length < 5 ? productCode.push({ value: '' }) : productCode" type="button"
                          class="mx-auto"
                          custom-class="bg-green-500 hover:bg-green-700"> + Product
        </button-component>
      </div>

    </form>

    <pre id="json" class="mt-10 text-slate-200 flex bg-gray-700 py-10">
      <span class="mx-auto">{{ result }}</span>
    </pre>
  </main>
</template>
