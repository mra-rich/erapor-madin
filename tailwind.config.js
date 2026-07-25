/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{php,html,js}",
    "./include/**/*.{php,html,js}",
    "./app/**/*.{php,html,js}",
    "./public/**/*.{php,html,js}",
    "./src/**/*.{css,js}"
  ],
  theme: {
    extend: {
      animation: {
        'border-spin': 'spin 4s linear infinite',
      }
    },
  },
  plugins: [],
}
