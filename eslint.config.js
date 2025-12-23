// eslint.config.js
import js from "@eslint/js";
import globals from "globals";
import jqueryPlugin from "eslint-plugin-jquery";

/** @type {import('eslint').Linter.Config[]} */
export default [
  // 1. Apply recommended ESLint rules to all JS files
  js.configs.recommended,

  {
    files: ["**/*.js"],
    languageOptions: {
      // 2. Specify the ECMAScript version and source type
      ecmaVersion: "latest",
      sourceType: "module", // Use "commonjs" if using require()

      // 3. Define global variables to prevent "no-undef" errors
      globals: {
        ...globals.browser, // Includes window, document, etc.
        ...globals.jquery,  // Specifically defines $ and jQuery
        display_jukebox: "readonly",
        shuffle: "readonly",
      },
    },
    // 4. Integrate the jQuery plugin for specialized rules
    plugins: {
      jquery: jqueryPlugin,
    },
    rules: {
      // 5. Example jQuery-specific rules to improve code quality
      "jquery/no-ajax": "off",        // Allow $.ajax calls
      "jquery/no-animate": "warn",    // Suggest CSS transitions instead
      "jquery/no-fade": "warn",
      //"jquery/no-global-selector": "error", // Avoid performance issues with $('some-selector')
      
      // Standard linting rules
      "semi": ["error", "always"],
      "no-unused-vars": "warn",
    },
  },
  
  // 6. Ignore third-party libraries or build folders
  {
    ignores: ["dist/", "vendor/", "node_modules/"],
  },
];
