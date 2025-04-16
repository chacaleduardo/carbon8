/** @type {import('tailwindcss').Config} */
export default {
  important: false,
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {
      boxShadow: {
        btn: "0 4px 4px rgba(0, 0, 0, 0.25)",
      },
      colors: {
        primary: "#2454C0",
        secondary: "#5E5E5E",
      },
      backgroundImage: {
        "gradient-radial": "radial-gradient(var(--tw-gradient-stops))",
        "gradient-conic":
          "conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))",
      },
      screens: {
        "3xl": "1600px",
        "4xl": "2000px",
        xs: "375px",
      },
    },
  },
  plugins: [],
};