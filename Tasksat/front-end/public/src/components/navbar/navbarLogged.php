<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta aí!</title>
    <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'verde': {
              DEFAULT: '#669f2a',
              50: '#E0FFC0',
              70: '#A2EC16',
              100 : '#b0de7f',
              150: '#A9CC68',
              200 : '#93d411',
              250: '#4F6E15',
              300 : '#467614',
            },
            'vermelho': {
              DEFAULT: '#d64919',
              100: '#e66d42',
              150: '#E72C22',
              200: '#903923',
              250: '#870B00',
            }
          }
        }
      }
    }
  </script>
</head>
<body>
    <nav class="flex justify-between items-center p-6 pl-3 pr-3 bg-verde w-full h-fit">
  <div class="flex gap-3 items-center">
    <a href="/Tasksat/index.php" class="font-bold text-3xl text-vermelho-250 hover:text-vermelho-150">Alerta aí!</a>
    <a href="#" class="flex m-1 text-green-950 font-bold hover:text-verde-70">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
      </svg>
      <p class="max-sm:hidden">Chamados</p>
    </a>
  </div>
  <div class="flex gap-3">
    <a href="/Tasksat/back-end/actions/exit.accont.php" class="flex m-1 text-green-950 font-bold hover:text-verde-70">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
      </svg>
      <p class="max-sm:hidden">Sair</p>
    </a>
  </div>
</nav>

</body>
</html>