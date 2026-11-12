export const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
export const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;
const MAX_DIMENSION = 1600;

/**
 * Shrink large photos in the browser before upload (saves mobile data and server space).
 * Uses createImageBitmap with EXIF orientation so phone photos are not rotated.
 * Returns the original file if it is already small, or if the browser cannot decode it.
 */
export async function prepareImage(file) {
  if (!ACCEPTED_TYPES.includes(file.type)) return file;
  if (typeof createImageBitmap !== 'function') return file;

  let bitmap;
  try {
    bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
  } catch {
    return file;
  }

  const { width, height } = bitmap;
  const scale = Math.min(1, MAX_DIMENSION / Math.max(width, height));
  if (scale === 1 && file.size <= 1.5 * 1024 * 1024) {
    bitmap.close?.();
    return file;
  }

  const canvas = document.createElement('canvas');
  canvas.width = Math.round(width * scale);
  canvas.height = Math.round(height * scale);
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#ffffff'; // flatten transparent PNGs onto white
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
  bitmap.close?.();

  const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
  if (!blob || blob.size >= file.size) return file;
  const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
  return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
}

/** Resolves true if the URL loads as an image in the browser. */
export function checkImageUrl(url, timeoutMs = 8000) {
  return new Promise((resolve) => {
    const img = new Image();
    const timer = setTimeout(() => {
      img.src = '';
      resolve(false);
    }, timeoutMs);
    img.onload = () => {
      clearTimeout(timer);
      resolve(img.naturalWidth > 0);
    };
    img.onerror = () => {
      clearTimeout(timer);
      resolve(false);
    };
    img.referrerPolicy = 'no-referrer';
    img.src = url;
  });
}
