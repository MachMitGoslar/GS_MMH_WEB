/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [  ],
  theme: {
    colors: {
      'gold': '#B09C41',
      'blue': '#8090FB',
      'green': '#869965',
      'red': '#B54D54',
      'orange': '#DFA926',
      'gray-dark': '#273444',
      'gray': '#8492a6',
      'gray-light': '#d3dce6',
      'white': '#fff'
    },
    fontFamily: {
      'sans': ['Lato', 'Inter'],
    },
    extend: {
      fontFamily: {
        'sans': ['Lato', 'Inter'],
      },
    },
  },
  plugins: [
    require('flowbite-typography')
  ],
}

