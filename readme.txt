Vue js
............

Vue js version 3
Vue is a popular JavaScript framework.
Vue is easy to learn.
Vue is a front-end JavaScript framework written in JavaScript.
User interfaces built in Vue updates automatically when data changes.
Similar frameworks to Vue are React and Angular, but Vue is more lightweight and easier to start with.
It is able to handle both simple and complex projects.
Its growing popularity and open-source community support.
It allows for a more efficient development process with a template-based syntax, two-way data binding, and a centralized state management.

vue js syntax
...............

const { createApp } = Vue;
createApp({
  data(){
    return{
      message : "Hello vue js 3",
    }
  }
}).mount('#app')


1 Importing createApp from Vue:

This line uses destructuring to import the createApp function from the Vue object.
createApp is a function provided by Vue.js 3 to create a new Vue application instance.

const { createApp } = Vue;


2 Creating the Vue application:

This part calls the createApp function and passes an options object to it. The options object contains the data function,
which returns an object with the reactive state of the application.
In this case, the state contains a single property message with the value 'Hello World!'.


3 Mounting the application to the DOM:

.mount('#app');

After creating the Vue application instance, the mount method is called with the CSS selector '#app'.
This tells Vue to mount the application to the DOM element with the ID app.
The Vue instance will manage this element and render the content defined in the data function.

Putting it all together, this script creates a Vue application with a reactive data property message and mounts 
it to the DOM element with the ID app.
The content of the message property will be displayed inside the #app element.


lifecycle flow
...................
beforeCreate: Logs a message before the instance is initialized.
created: Logs a message after the instance is created.
beforeMount: Logs a message before the instance is mounted to the DOM.
mounted: Logs a message after the instance is mounted to the DOM.
beforeUpdate: Logs a message before the data is updated.
updated: Logs a message after the data is updated.
beforeUnmount: Logs a message before the instance is destroyed.
unmounted: Logs a message after the instance is destroyed.


Vue Directives
..................
Vue directives are special tokens in the markup that tell the library to do something to a DOM element. 
They are prefixed with v- to indicate that they are special attributes provided by Vue.
Vue directives are special HTML attributes with the prefix v- that give the HTML tag extra functionality.


List of Common Vue Directives
..............................
v-bind: Dynamically bind one or more attributes, or a component prop, to an expression.
v-model: Create a two-way binding on an input, textarea, or select element.
v-if: Conditionally render the element based on the truthiness of the expression.
v-else-if: Denote the "else if" block for v-if.
v-else: Denote the "else" block for v-if.
v-for: Render the element or template block multiple times based on the source data.
v-on: Attach event listeners to the element.
v-show: Toggle the element's visibility based on the truthiness of the expression.
v-pre: Skip compilation for this element and all its children.
v-cloak: Keep the element and its children hidden until Vue's compilation is done.
v-once: Render the element and component once only.


Types of events!
...................

Mouse Events
@click
@dblclick
@contextmenu
@mousedown
@mouseup
@mouseenter
@mouseleave
@mouseover
@mouseout
@mousemove

Keyboard Events
@keydown
@keyup
@keypress

Form Events
@input
@change
@focus
@blur
@submit

Clipboard Events
@copy
@cut
@paste

Other Events
@scroll
@resize
@drag
@drop



Method
....................

In Vue.js, the methods option is where you define functions (methods) that can 
be used in your template or elsewhere in your component.
These methods are typically used to handle events,
perform actions, or manipulate data.
Defined inside the methods property of your Vue component or app.


Axios
..................
Why use Axios in big projects?

Simple and consistent API for all HTTP methods (GET, POST, PUT, DELETE, etc.)
Supports request/response interceptors (for auth, error handling, etc.)
Handles JSON automatically
Works well with async/await
Easily configurable for base URLs, headers, and timeouts
Well-documented and actively maintained


Main Features of Axios
...........................
Promise-based: Works with async/await and .then()/.catch().
Supports all HTTP methods: GET, POST, PUT, PATCH, DELETE, etc.
Automatic JSON data transformation: Sends and receives JSON easily.
Request and response interceptors: Add logic before requests or after responses (e.g., auth, logging).
Automatic request cancellation: Useful for debouncing or aborting requests.
Client-side and server-side: Works in browsers and Node.js.
Custom headers: Easily set headers for requests.
Timeouts: Set request timeouts.
Base URL and global config: Set a base URL for all requests.
Upload and download progress: Track progress for large files.
CSRF/XSRF protection: Handles cookies and tokens for security.






Component
.................

A component in Vue.js is a reusable, self-contained block of code that controls a part of the user interface.
Each component has its own template, logic, and data.

To break your app into smaller, manageable pieces (like buttons, forms, cards, etc.)
To reuse UI blocks across your app
To organize code for better readability and maintainability





Useful Links
..................
https://axios-http.com/docs/intro
https://axios-http.com/docs/req_config
https://axios-http.com/docs/interceptors
https://vueschool.io/courses/vuejs-3-fundamentals