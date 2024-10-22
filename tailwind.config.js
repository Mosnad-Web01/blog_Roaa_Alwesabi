module.exports = {
  content: [
    './views/**/*.php',
    './views/**/*.html',
    './assets/**/*.js',
    './index.php',
  ],
  darkMode: 'class', // يمكن أن يكون 'media' أو 'class'
  theme: {
    extend: {
      colors: {
        background: "var(--background)",
        foreground: "var(--foreground)",
      },
    },
  },
  plugins: [],
};
