<script setup lang="ts">
  import { authDestination } from '~/utils/auth'

  definePageMeta({
    layout: false
  })

  const { fetchUser } = useAuth()
  const account = await fetchUser()
  await navigateTo(account ? authDestination(undefined, account.role) : '/login', {
    replace: true
  })
</script>

<template>
  <div />
</template>
