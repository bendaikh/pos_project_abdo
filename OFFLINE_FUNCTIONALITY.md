# Offline Functionality Documentation

## Overview
Your POS system now supports **full offline functionality**, allowing cashiers to continue working even without internet connection. All sales are saved locally and automatically synced when the connection is restored.

## Features Implemented

### 1. **Service Worker (PWA)**
- Caches all static assets (CSS, JS, HTML)
- App loads instantly even without internet
- Serves cached responses when offline

### 2. **IndexedDB Local Storage**
- Stores products, categories, and settings locally
- Saves pending sales when offline
- Fast access to data without API calls

### 3. **Offline Detection**
- Real-time connection status monitoring
- Visual indicators for offline mode
- Automatic sync when connection restored

### 4. **Pending Sales Queue**
- Sales are saved locally when offline
- Automatic background sync when online
- Manual sync option available
- Error handling and retry logic

### 5. **Visual Indicators**
- Orange badge when offline
- Blue badge showing pending sales count
- Success notification after sync
- Error messages if sync fails

## How It Works

### When Online (Normal Mode)
1. App fetches fresh data from server
2. Automatically caches data for offline use
3. Sales are processed immediately
4. Real-time synchronization

### When Offline
1. App detects connection loss
2. Switches to cached data automatically
3. Sales are saved to local queue
4. Orange "Mode Hors ligne" badge appears

### When Connection Restored
1. App detects connection restored
2. Automatically syncs pending sales
3. Shows sync progress
4. Displays success notification

## User Experience

### Offline Indicators
Located at bottom-right of screen:

1. **Offline Badge (Orange)**
   - "Mode Hors ligne"
   - "Les ventes seront synchronisées automatiquement"

2. **Pending Sales Badge (Blue)**
   - Shows number of pending sales
   - Click to manually trigger sync
   - Animated during sync

3. **Success Badge (Yellow)**
   - "Synchronisation réussie!"
   - Auto-disappears after 3 seconds

4. **Error Badge (Red)**
   - Shows if sync fails
   - Displays number of failed sales

## Technical Details

### Files Created

1. **`public/service-worker.js`**
   - Service worker for caching
   - Background sync support

2. **`public/offline.html`**
   - Fallback page for navigation requests

3. **`resources/js/utils/indexeddb.js`**
   - IndexedDB utility functions
   - CRUD operations for local storage

4. **`resources/js/stores/offline.js`**
   - Pinia store for offline state management
   - Sync logic and queue management

5. **`resources/js/components/OfflineIndicator.vue`**
   - Visual feedback component

### Database Structure (IndexedDB)

- **articles**: Product data with indexes on category_id, sku, is_favorite
- **categories**: Category data
- **pending_sales**: Offline sales queue with sync status
- **settings**: App settings for offline use
- **customers**: Customer data (cached)

### Sync Process

1. Sales created offline get a temporary ID
2. Added to `pending_sales` store with `synced: false`
3. When online, automatic sync starts
4. Each sale is sent to API
5. On success, marked as synced and removed
6. On failure, kept in queue for retry

## Usage Instructions

### For Cashiers

1. **Normal Operation**
   - Use POS as usual
   - No special actions needed

2. **When Internet Drops**
   - Orange badge appears
   - Continue selling normally
   - Sales are saved locally

3. **When Internet Returns**
   - Blue badge shows pending count
   - Sync happens automatically
   - Or click badge to sync manually

### For Administrators

1. **Monitoring**
   - Check pending sales count in indicator
   - Review sync status
   - Handle sync errors if any

2. **Manual Sync**
   - Click the blue pending sales badge
   - Or wait for automatic sync

3. **Troubleshooting**
   - If sales don't sync, check internet
   - Error badge shows failed syncs
   - Sales remain safe in local storage

## Benefits

✅ **Reliability**: Never lose sales due to internet issues
✅ **Performance**: Faster loading with cached data
✅ **Continuity**: Seamless transition between online/offline
✅ **User-Friendly**: Clear visual feedback
✅ **Automatic**: No manual intervention needed
✅ **Secure**: Data persists until successfully synced

## Limitations

⚠️ **Inventory Updates**: Stock quantities may not reflect real-time changes when offline
⚠️ **New Products**: Products added while you're offline won't appear until connection restored
⚠️ **Multi-Device**: Offline sales from multiple devices sync separately
⚠️ **Storage Limit**: Browser storage has limits (usually several hundred MB)

## Best Practices

1. **Keep Connection**: Maintain internet when possible for real-time sync
2. **Monitor Indicator**: Check pending sales regularly
3. **Sync Frequently**: Don't let too many sales pile up offline
4. **Clear Cache**: Periodically clear old data to free space
5. **Train Staff**: Ensure cashiers understand offline indicators

## Future Enhancements

Potential improvements:
- Conflict resolution for concurrent edits
- Selective sync options
- Offline reporting
- Background periodic sync
- Push notifications for sync status
- Admin dashboard for offline sales monitoring

## Support

If you encounter issues:
1. Check browser console for errors
2. Verify IndexedDB is enabled
3. Clear browser cache and reload
4. Check service worker registration
5. Review network tab for failed requests

---

**Your POS system is now enterprise-ready with offline capabilities!** 🎉
