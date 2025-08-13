# Vue.js 3 Complete Guide - A to Z

## 📚 **Table of Contents**

1. [What is Vue.js 3?](#what-is-vuejs-3)
2. [Installation & Setup](#installation--setup)
3. [Basic Concepts](#basic-concepts)
4. [Template Syntax](#template-syntax)
5. [Directives (A to Z)](#directives-a-to-z)
6. [Data & Reactivity](#data--reactivity)
7. [Methods](#methods)
8. [Computed Properties](#computed-properties)
9. [Watchers](#watchers)
10. [Event Handling](#event-handling)
11. [Form Handling](#form-handling)
12. [Components](#components)
13. [Props & Custom Events](#props--custom-events)
14. [Slots](#slots)
15. [Lifecycle Hooks](#lifecycle-hooks)
16. [Composition API](#composition-api)
17. [Vue Router](#vue-router)
18. [State Management (Pinia)](#state-management-pinia)
19. [API Integration](#api-integration)
20. [Best Practices](#best-practices)
21. [Real-World Examples](#real-world-examples)

---

## 1. **What is Vue.js 3?**

Vue.js is a **progressive JavaScript framework** for building user interfaces and single-page applications (SPAs).

### **Key Features:**
- ✅ **Reactive Data Binding**
- ✅ **Component-Based Architecture**
- ✅ **Virtual DOM**
- ✅ **Progressive Enhancement**
- ✅ **Easy Learning Curve**
- ✅ **TypeScript Support**
- ✅ **Composition API**

### **Why Vue.js 3?**
- **Better Performance** than Vue 2
- **Composition API** for better code organization
- **Better TypeScript** support
- **Multiple root elements** support
- **Tree-shaking** for smaller bundle sizes

---

## 2. **Installation & Setup**

### **A. CDN Method (Quick Start):**
```html
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
```

### **B. Using Vite (Recommended for Projects):**
```bash
npm create vue@latest my-project
cd my-project
npm install
npm run dev
```

### **C. Basic HTML Template:**
```html
<!DOCTYPE html>
<html>
<head>
  <title>Vue.js 3 App</title>
</head>
<body>
  <div id="app">
    {{ message }}
  </div>
  
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  <script>
    const { createApp } = Vue;
    createApp({
      data() {
        return {
          message: 'Hello Vue 3!'
        }
      }
    }).mount('#app');
  </script>
</body>
</html>
```

---

## 3. **Basic Concepts**

### **A. Vue Instance:**
```javascript
const app = createApp({
  data() {
    return {
      message: 'Hello World'
    }
  },
  methods: {
    greet() {
      alert('Hello!');
    }
  }
});
app.mount('#app');
```

### **B. Template Syntax:**
```html
<!-- Text Interpolation -->
<span>Message: {{ msg }}</span>

<!-- Raw HTML -->
<span v-html="rawHtml"></span>

<!-- Attribute Binding -->
<div v-bind:id="dynamicId"></div>
<div :id="dynamicId"></div> <!-- shorthand -->

<!-- JavaScript Expressions -->
{{ number + 1 }}
{{ ok ? 'YES' : 'NO' }}
{{ message.split('').reverse().join('') }}
```

---

## 4. **Template Syntax**

### **A. Mustache Syntax ({{ }}):**
```html
<span>{{ message }}</span>
<span>{{ number + 1 }}</span>
<span>{{ ok ? 'YES' : 'NO' }}</span>
```

### **B. Attribute Binding:**
```html
<!-- Long form -->
<img v-bind:src="imageSrc">
<button v-bind:disabled="isButtonDisabled">

<!-- Shorthand -->
<img :src="imageSrc">
<button :disabled="isButtonDisabled">
```

### **C. Event Binding:**
```html
<!-- Long form -->
<button v-on:click="handleClick">Click me</button>

<!-- Shorthand -->
<button @click="handleClick">Click me</button>
```

---

## 5. **Directives (A to Z)**

### **A. v-bind (Attribute Binding):**
**Purpose:** Dynamically bind attributes to data
```html
<img :src="imageUrl" :alt="imageAlt">
<div :class="{ active: isActive }">
<button :disabled="isDisabled">Submit</button>
```

### **B. v-model (Two-way Data Binding):**
**Purpose:** Create two-way data binding on form inputs
```html
<input v-model="message" placeholder="Type something">
<textarea v-model="text"></textarea>
<input type="checkbox" v-model="checked">
<select v-model="selected">
  <option value="A">Option A</option>
  <option value="B">Option B</option>
</select>
```

### **C. v-if, v-else-if, v-else (Conditional Rendering):**
**Purpose:** Conditionally render elements
```html
<div v-if="type === 'A'">
  Type A
</div>
<div v-else-if="type === 'B'">
  Type B
</div>
<div v-else>
  Not A or B
</div>
```

### **D. v-show (Toggle Visibility):**
**Purpose:** Toggle element visibility with CSS
```html
<div v-show="isVisible">This will be hidden with display: none</div>
```

### **E. v-for (List Rendering):**
**Purpose:** Render lists of data
```html
<!-- Array -->
<li v-for="(item, index) in items" :key="item.id">
  {{ index }} - {{ item.name }}
</li>

<!-- Object -->
<li v-for="(value, key) in object" :key="key">
  {{ key }}: {{ value }}
</li>

<!-- Numbers -->
<span v-for="n in 10" :key="n">{{ n }}</span>
```

### **F. v-on (Event Handling):**
**Purpose:** Listen to DOM events
```html
<button @click="handleClick">Click</button>
<button @click="count++">Increment</button>
<button @click="handleClick($event)">With Event</button>

<!-- Event Modifiers -->
<form @submit.prevent="onSubmit">
<button @click.stop="handleClick">
<input @keyup.enter="handleEnter">
```

### **G. v-text (Text Content):**
**Purpose:** Update element's textContent
```html
<span v-text="message"></span>
<!-- Same as: <span>{{ message }}</span> -->
```

### **H. v-html (HTML Content):**
**Purpose:** Update element's innerHTML
```html
<div v-html="htmlContent"></div>
<!-- Warning: Only use with trusted content! -->
```

### **I. v-pre (Skip Compilation):**
**Purpose:** Skip compilation for this element
```html
<span v-pre>{{ this will not be compiled }}</span>
```

### **J. v-cloak (Hide Uncompiled Templates):**
**Purpose:** Hide elements until Vue is ready
```html
<style>
[v-cloak] { display: none; }
</style>
<div v-cloak>{{ message }}</div>
```

### **K. v-once (Render Once):**
**Purpose:** Render element only once
```html
<div v-once>{{ message }}</div>
<!-- Will not update even if message changes -->
```

### **L. v-slot (Slots):**
**Purpose:** Define named slots for content
```html
<template v-slot:header>
  <h1>Header content</h1>
</template>

<!-- Shorthand -->
<template #header>
  <h1>Header content</h1>
</template>
```

---

## 6. **Data & Reactivity**

### **A. Data Function:**
```javascript
data() {
  return {
    message: 'Hello',
    count: 0,
    user: {
      name: 'John',
      age: 30
    },
    items: ['apple', 'banana', 'cherry']
  }
}
```

### **B. Reactive Data:**
```javascript
import { ref, reactive } from 'vue'

// Composition API
const count = ref(0)
const state = reactive({
  name: 'John',
  age: 30
})
```

---

## 7. **Methods**

**Purpose:** Define functions that can be called from templates or other methods

```javascript
methods: {
  // Simple method
  greet() {
    alert('Hello!');
  },
  
  // Method with parameters
  greetUser(name) {
    alert(`Hello, ${name}!`);
  },
  
  // Method that updates data
  increment() {
    this.count++;
  },
  
  // Async method
  async fetchData() {
    const response = await fetch('/api/data');
    this.data = await response.json();
  }
}
```

**Usage in Template:**
```html
<button @click="greet">Say Hello</button>
<button @click="greetUser('Vue')">Greet Vue</button>
<button @click="increment">Count: {{ count }}</button>
```

---

## 8. **Computed Properties**

**Purpose:** Create derived state that automatically updates when dependencies change

```javascript
computed: {
  // Simple computed property
  fullName() {
    return `${this.firstName} ${this.lastName}`;
  },
  
  // Computed property with getter and setter
  fullNameWithSetter: {
    get() {
      return `${this.firstName} ${this.lastName}`;
    },
    set(value) {
      const names = value.split(' ');
      this.firstName = names[0];
      this.lastName = names[names.length - 1];
    }
  },
  
  // Complex computed property
  expensiveValue() {
    // This will only re-run when dependencies change
    return this.items.filter(item => item.price > 100)
                    .reduce((sum, item) => sum + item.price, 0);
  }
}
```

**Computed vs Methods:**
- **Computed:** Cached, only re-runs when dependencies change
- **Methods:** Always runs when called

---

## 9. **Watchers**

**Purpose:** Perform side effects in response to data changes

```javascript
watch: {
  // Simple watcher
  message(newValue, oldValue) {
    console.log(`Message changed from ${oldValue} to ${newValue}`);
  },
  
  // Deep watcher for objects
  user: {
    handler(newValue, oldValue) {
      console.log('User object changed');
    },
    deep: true
  },
  
  // Immediate watcher
  count: {
    handler(newValue) {
      console.log(`Count is now ${newValue}`);
    },
    immediate: true
  }
}
```

**Composition API Watchers:**
```javascript
import { watch, watchEffect } from 'vue'

// Watch a single ref
watch(count, (newCount, oldCount) => {
  console.log(`Count: ${oldCount} -> ${newCount}`);
});

// Watch multiple sources
watch([count, name], ([newCount, newName], [oldCount, oldName]) => {
  console.log('Count or name changed');
});

// Watch effect (automatically tracks dependencies)
watchEffect(() => {
  console.log(`Count is ${count.value}`);
});
```

---

## 10. **Event Handling**

### **A. Basic Events:**
```html
<button @click="handleClick">Click</button>
<input @input="handleInput" @focus="handleFocus">
<form @submit="handleSubmit">
```

### **B. Event Modifiers:**
```html
<!-- Prevent default behavior -->
<form @submit.prevent="handleSubmit">

<!-- Stop event propagation -->
<button @click.stop="handleClick">

<!-- Key modifiers -->
<input @keyup.enter="handleEnter">
<input @keyup.esc="handleEscape">
<input @keyup.ctrl.a="handleCtrlA">

<!-- Mouse modifiers -->
<button @click.left="handleLeftClick">
<button @click.right="handleRightClick">
<button @click.middle="handleMiddleClick">

<!-- System modifiers -->
<button @click.ctrl="handleCtrlClick">
<button @click.shift="handleShiftClick">
```

### **C. Custom Events:**
```javascript
// In child component
this.$emit('custom-event', eventData);

// In parent template
<child-component @custom-event="handleCustomEvent">
```

---

## 11. **Form Handling**

### **A. Form Input Binding:**
```html
<!-- Text Input -->
<input v-model="message" type="text">

<!-- Textarea -->
<textarea v-model="message"></textarea>

<!-- Checkbox -->
<input v-model="checked" type="checkbox">

<!-- Radio -->
<input v-model="picked" type="radio" value="A">
<input v-model="picked" type="radio" value="B">

<!-- Select -->
<select v-model="selected">
  <option value="A">Option A</option>
  <option value="B">Option B</option>
</select>

<!-- Multiple Select -->
<select v-model="selected" multiple>
  <option value="A">Option A</option>
  <option value="B">Option B</option>
</select>
```

### **B. Form Modifiers:**
```html
<!-- Lazy update (on change, not input) -->
<input v-model.lazy="message">

<!-- Convert to number -->
<input v-model.number="age" type="number">

<!-- Trim whitespace -->
<input v-model.trim="message">
```

### **C. Form Validation Example:**
```html
<form @submit.prevent="handleSubmit">
  <div>
    <input 
      v-model="form.email" 
      type="email" 
      :class="{ 'error': errors.email }"
      required
    >
    <span v-if="errors.email" class="error-message">
      {{ errors.email }}
    </span>
  </div>
  <button type="submit" :disabled="!isFormValid">Submit</button>
</form>
```

---

## 12. **Components**

### **A. Component Registration:**
```javascript
// Global Registration
app.component('my-component', {
  template: `<div>{{ message }}</div>`,
  data() {
    return {
      message: 'Hello from component!'
    }
  }
});

// Local Registration
export default {
  components: {
    MyComponent: {
      template: `<div>Local Component</div>`
    }
  }
}
```

### **B. Single File Components (.vue):**
```vue
<template>
  <div class="my-component">
    <h2>{{ title }}</h2>
    <button @click="increment">Count: {{ count }}</button>
  </div>
</template>

<script>
export default {
  name: 'MyComponent',
  data() {
    return {
      title: 'My Component',
      count: 0
    }
  },
  methods: {
    increment() {
      this.count++;
    }
  }
}
</script>

<style scoped>
.my-component {
  padding: 20px;
  border: 1px solid #ccc;
}
</style>
```

---

## 13. **Props & Custom Events**

### **A. Props (Parent to Child):**
```javascript
// Child Component
export default {
  props: {
    // Simple prop
    message: String,
    
    // Prop with validation
    count: {
      type: Number,
      default: 0,
      required: true,
      validator(value) {
        return value >= 0;
      }
    },
    
    // Multiple types
    id: [String, Number],
    
    // Object prop
    user: {
      type: Object,
      default() {
        return { name: '', age: 0 };
      }
    }
  }
}
```

**Parent Template:**
```html
<child-component 
  :message="parentMessage" 
  :count="parentCount"
  :user="currentUser"
>
</child-component>
```

### **B. Custom Events (Child to Parent):**
```javascript
// Child Component
methods: {
  handleClick() {
    // Emit event to parent
    this.$emit('item-clicked', this.item);
    this.$emit('update:count', this.count + 1);
  }
}

// Define emitted events (Vue 3)
emits: ['item-clicked', 'update:count']
```

**Parent Template:**
```html
<child-component 
  @item-clicked="handleItemClick"
  @update:count="count = $event"
>
</child-component>
```

---

## 14. **Slots**

### **A. Basic Slots:**
```vue
<!-- Child Component -->
<template>
  <div class="container">
    <header>
      <slot name="header"></slot>
    </header>
    <main>
      <slot></slot> <!-- Default slot -->
    </main>
    <footer>
      <slot name="footer"></slot>
    </footer>
  </div>
</template>

<!-- Parent Usage -->
<child-component>
  <template #header>
    <h1>Page Title</h1>
  </template>
  
  <p>Main content goes here</p>
  
  <template #footer>
    <p>Footer content</p>
  </template>
</child-component>
```

### **B. Scoped Slots:**
```vue
<!-- Child Component -->
<template>
  <div>
    <slot :user="user" :isActive="isActive"></slot>
  </div>
</template>

<!-- Parent Usage -->
<child-component>
  <template #default="{ user, isActive }">
    <div :class="{ active: isActive }">
      {{ user.name }}
    </div>
  </template>
</child-component>
```

---

## 15. **Lifecycle Hooks**

```javascript
export default {
  // Before component is created
  beforeCreate() {
    console.log('beforeCreate: Component instance is being created');
  },
  
  // Component is created
  created() {
    console.log('created: Component instance is created');
    // Good place to fetch data
  },
  
  // Before component is mounted
  beforeMount() {
    console.log('beforeMount: Component is about to be mounted');
  },
  
  // Component is mounted
  mounted() {
    console.log('mounted: Component is mounted to DOM');
    // DOM is available, good for DOM manipulation
  },
  
  // Before component is updated
  beforeUpdate() {
    console.log('beforeUpdate: Data changed, before DOM update');
  },
  
  // Component is updated
  updated() {
    console.log('updated: DOM has been updated');
  },
  
  // Before component is unmounted
  beforeUnmount() {
    console.log('beforeUnmount: Component is about to be unmounted');
    // Cleanup timers, event listeners, etc.
  },
  
  // Component is unmounted
  unmounted() {
    console.log('unmounted: Component is unmounted');
  }
}
```

---

## 16. **Composition API**

### **A. Basic Setup:**
```javascript
import { ref, reactive, computed, onMounted } from 'vue'

export default {
  setup() {
    // Reactive data
    const count = ref(0)
    const state = reactive({
      name: 'John',
      age: 30
    })
    
    // Computed
    const doubleCount = computed(() => count.value * 2)
    
    // Methods
    const increment = () => {
      count.value++
    }
    
    // Lifecycle
    onMounted(() => {
      console.log('Component mounted')
    })
    
    // Return what template can use
    return {
      count,
      state,
      doubleCount,
      increment
    }
  }
}
```

### **B. Composition Functions (Composables):**
```javascript
// useCounter.js
import { ref } from 'vue'

export function useCounter(initialValue = 0) {
  const count = ref(initialValue)
  
  const increment = () => count.value++
  const decrement = () => count.value--
  const reset = () => count.value = initialValue
  
  return {
    count,
    increment,
    decrement,
    reset
  }
}

// In component
import { useCounter } from './composables/useCounter'

export default {
  setup() {
    const { count, increment, decrement } = useCounter(10)
    
    return {
      count,
      increment,
      decrement
    }
  }
}
```

---

## 17. **Vue Router**

### **A. Installation:**
```bash
npm install vue-router@4
```

### **B. Basic Setup:**
```javascript
// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import About from '../views/About.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/about', component: About },
  { path: '/user/:id', component: User, props: true }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router

// main.js
import { createApp } from 'vue'
import router from './router'
import App from './App.vue'

createApp(App).use(router).mount('#app')
```

### **C. Router Usage:**
```vue
<template>
  <div>
    <!-- Navigation -->
    <router-link to="/">Home</router-link>
    <router-link to="/about">About</router-link>
    
    <!-- Route component renders here -->
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  methods: {
    // Programmatic navigation
    goToAbout() {
      this.$router.push('/about')
    },
    
    goBack() {
      this.$router.go(-1)
    }
  }
}
</script>
```

---

## 18. **State Management (Pinia)**

### **A. Installation:**
```bash
npm install pinia
```

### **B. Store Setup:**
```javascript
// stores/counter.js
import { defineStore } from 'pinia'

export const useCounterStore = defineStore('counter', {
  state: () => ({
    count: 0,
    name: 'Vue'
  }),
  
  getters: {
    doubleCount: (state) => state.count * 2
  },
  
  actions: {
    increment() {
      this.count++
    },
    
    async fetchUserData() {
      const response = await fetch('/api/user')
      this.userData = await response.json()
    }
  }
})

// main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.mount('#app')
```

### **C. Using Store in Components:**
```vue
<template>
  <div>
    <p>Count: {{ counter.count }}</p>
    <p>Double: {{ counter.doubleCount }}</p>
    <button @click="counter.increment">Increment</button>
  </div>
</template>

<script>
import { useCounterStore } from '@/stores/counter'

export default {
  setup() {
    const counter = useCounterStore()
    
    return {
      counter
    }
  }
}
</script>
```

---

## 19. **API Integration**

### **A. Using Fetch:**
```javascript
export default {
  data() {
    return {
      users: [],
      loading: false,
      error: null
    }
  },
  
  async mounted() {
    await this.fetchUsers()
  },
  
  methods: {
    async fetchUsers() {
      try {
        this.loading = true
        const response = await fetch('/api/users')
        
        if (!response.ok) {
          throw new Error('Failed to fetch users')
        }
        
        this.users = await response.json()
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },
    
    async createUser(userData) {
      try {
        const response = await fetch('/api/users', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(userData)
        })
        
        if (response.ok) {
          await this.fetchUsers() // Refresh list
        }
      } catch (error) {
        console.error('Error creating user:', error)
      }
    }
  }
}
```

### **B. Using Axios:**
```javascript
import axios from 'axios'

// Create axios instance
const api = axios.create({
  baseURL: '/api',
  timeout: 10000,
})

// Request interceptor
api.interceptors.request.use(config => {
  // Add auth token
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response interceptor
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response.status === 401) {
      // Handle unauthorized
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

export default {
  methods: {
    async fetchUsers() {
      try {
        const response = await api.get('/users')
        this.users = response.data
      } catch (error) {
        console.error('Error fetching users:', error)
      }
    }
  }
}
```

---

## 20. **Best Practices**

### **A. Component Organization:**
- Use **Single File Components** (.vue files)
- Keep components **small and focused**
- Use **PascalCase** for component names
- Use **descriptive component names**

### **B. Data Management:**
- Use **computed properties** for derived state
- Use **methods** for actions and event handlers
- Use **watchers** sparingly, prefer computed properties
- Keep **data function pure**

### **C. Performance:**
- Use **v-show** vs **v-if** appropriately
- Add **:key** to v-for items
- Use **v-once** for static content
- **Lazy load** components when needed

### **D. Code Style:**
- Use **consistent indentation**
- Use **meaningful variable names**
- Add **comments** for complex logic
- Follow **Vue Style Guide**

### **E. Security:**
- **Never use v-html** with user input
- **Validate props** properly
- **Sanitize data** from APIs
- Use **CSP headers**

---

## 21. **Real-World Examples**

### **A. Complete CRUD Component:**
```vue
<template>
  <div class="user-manager">
    <h2>User Management</h2>
    
    <!-- Add User Form -->
    <form @submit.prevent="addUser">
      <input v-model="newUser.name" placeholder="Name" required>
      <input v-model="newUser.email" placeholder="Email" required>
      <button type="submit" :disabled="loading">Add User</button>
    </form>
    
    <!-- Users List -->
    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else>
      <div v-for="user in users" :key="user.id" class="user-item">
        <span>{{ user.name }} ({{ user.email }})</span>
        <button @click="editUser(user)">Edit</button>
        <button @click="deleteUser(user.id)" class="danger">Delete</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'UserManager',
  
  data() {
    return {
      users: [],
      newUser: { name: '', email: '' },
      loading: false,
      error: null
    }
  },
  
  async created() {
    await this.fetchUsers()
  },
  
  methods: {
    async fetchUsers() {
      try {
        this.loading = true
        this.error = null
        const response = await axios.get('/api/users')
        this.users = response.data
      } catch (error) {
        this.error = 'Failed to fetch users'
      } finally {
        this.loading = false
      }
    },
    
    async addUser() {
      try {
        await axios.post('/api/users', this.newUser)
        this.newUser = { name: '', email: '' }
        await this.fetchUsers()
      } catch (error) {
        this.error = 'Failed to add user'
      }
    },
    
    async deleteUser(id) {
      if (confirm('Are you sure?')) {
        try {
          await axios.delete(`/api/users/${id}`)
          await this.fetchUsers()
        } catch (error) {
          this.error = 'Failed to delete user'
        }
      }
    }
  }
}
</script>

<style scoped>
.user-manager {
  padding: 20px;
}

.user-item {
  display: flex;
  justify-content: space-between;
  padding: 10px;
  border: 1px solid #ccc;
  margin: 5px 0;
}

.error {
  color: red;
  padding: 10px;
  background: #ffeaa7;
  border-radius: 4px;
}

.danger {
  background: #d63031;
  color: white;
}
</style>
```

---

## 🎯 **Summary**

This guide covers **everything** you need to know about Vue.js 3:

✅ **Basic concepts** and setup  
✅ **All directives** with examples  
✅ **Components** and communication  
✅ **Routing** and navigation  
✅ **State management** with Pinia  
✅ **API integration** patterns  
✅ **Best practices** for production  
✅ **Real-world examples** and patterns  

### **Next Steps:**
1. **Practice** each concept with small examples
2. **Build projects** using these patterns
3. **Explore advanced topics** like testing, SSR, and performance optimization
4. **Join the Vue community** and contribute to open source

---

**Happy Vue.js 3 Development! 🚀**
