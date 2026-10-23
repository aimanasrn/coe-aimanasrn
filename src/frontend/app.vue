<template>
  <UApp
    :tooltip="{ delayDuration: 200 }"
    :toaster="{ position: 'bottom-right', expand: true, duration: 3000 }"
  >
    <StudyMartPageLoader />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>
