// IndexedDB utility for offline storage
const DB_NAME = 'greenpos-db';
const DB_VERSION = 1;

const STORES = {
    ARTICLES: 'articles',
    CATEGORIES: 'categories',
    PENDING_SALES: 'pending_sales',
    SETTINGS: 'settings',
    CUSTOMERS: 'customers',
};

let db = null;

// Initialize database
export async function initDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => {
            console.error('Error opening IndexedDB:', request.error);
            reject(request.error);
        };

        request.onsuccess = () => {
            db = request.result;
            console.log('IndexedDB initialized successfully');
            resolve(db);
        };

        request.onupgradeneeded = (event) => {
            const database = event.target.result;

            // Articles store
            if (!database.objectStoreNames.contains(STORES.ARTICLES)) {
                const articlesStore = database.createObjectStore(STORES.ARTICLES, { keyPath: 'id' });
                articlesStore.createIndex('category_id', 'category_id', { unique: false });
                articlesStore.createIndex('sku', 'sku', { unique: false });
                articlesStore.createIndex('is_favorite', 'is_favorite', { unique: false });
            }

            // Categories store
            if (!database.objectStoreNames.contains(STORES.CATEGORIES)) {
                database.createObjectStore(STORES.CATEGORIES, { keyPath: 'id' });
            }

            // Pending sales store (for offline transactions)
            if (!database.objectStoreNames.contains(STORES.PENDING_SALES)) {
                const pendingSalesStore = database.createObjectStore(STORES.PENDING_SALES, { 
                    keyPath: 'temp_id',
                    autoIncrement: true 
                });
                pendingSalesStore.createIndex('created_at', 'created_at', { unique: false });
                pendingSalesStore.createIndex('synced', 'synced', { unique: false });
            }

            // Settings store
            if (!database.objectStoreNames.contains(STORES.SETTINGS)) {
                database.createObjectStore(STORES.SETTINGS, { keyPath: 'key' });
            }

            // Customers store
            if (!database.objectStoreNames.contains(STORES.CUSTOMERS)) {
                database.createObjectStore(STORES.CUSTOMERS, { keyPath: 'id' });
            }

            console.log('Database schema created');
        };
    });
}

// Generic CRUD operations
export async function addItem(storeName, item) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.add(item);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function putItem(storeName, item) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.put(item);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getItem(storeName, key) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.get(key);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getAllItems(storeName) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function deleteItem(storeName, key) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(key);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function clearStore(storeName) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.clear();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

// Bulk operations
export async function bulkPut(storeName, items) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        
        let completed = 0;
        const errors = [];

        items.forEach((item, index) => {
            const request = store.put(item);
            
            request.onsuccess = () => {
                completed++;
                if (completed === items.length) {
                    resolve({ success: true, errors });
                }
            };
            
            request.onerror = () => {
                errors.push({ index, item, error: request.error });
                completed++;
                if (completed === items.length) {
                    resolve({ success: errors.length === 0, errors });
                }
            };
        });
    });
}

// Query by index
export async function getByIndex(storeName, indexName, value) {
    if (!db) await initDB();
    
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const index = store.index(indexName);
        const request = index.getAll(value);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export { STORES };
