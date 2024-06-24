/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [ 
    './site/**/*.{html,js,php}',
   ],
  theme: {

    fontFamily: {
      'sans': ['Lato', 'Inter'],
    },
    extend: {
      colors: {
        'gold': {
          DEFAULT: '#b09c41',
          '50': '#f8f8ee',
          '100': '#efefd2',
          '200': '#e1dea7',
          '300': '#cfc775',
          '400': '#c0b04f',
          '500': '#b09c41',
          '600': '#987e36',
          '700': '#7a602e',
          '800': '#674f2c',
          '900': '#59432a',
          '950': '#332415'
        },
        'blue': '#8090FB',
        'green': {
          DEFAULT: "#869965",
          '50': '#f4f5f0',
          '100': '#e6eadd',
          '200': '#d0d7bf',
          '300': '#b1bd99',
          '400': '#94a477',
          '500': '#869965',
          '600': '#5c6b45',
          '700': '#485338',
          '800': '#3c4430',
          '900': '#353c2b',
          '950': '#1a1f14',
        },
        'gray': {
          '50': '#fafaf9',
          '100': '#f5f5f4',
          '200': '#e7e5e4',
          '300': '#d6d3d1',
          '400': '#a8a29e',
          '500': '#78716c',
          '600': '#57534e',
          '700': '#44403c',
          '800': '#292524',
          '900': '#1c1917',
          '950': '#0c0a09',
          DEFAULT: '#78716c'
        },
        'red': '#B54D54',
        'orange': '#DFA926',
      },
      fontFamily: {
        'sans': ['Lato', 'Inter'],
      },
    },
  },
  plugins: [
    require('flowbite-typography')
  ],
}

