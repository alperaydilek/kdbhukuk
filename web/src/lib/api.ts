// KDB Hukuk — Laravel API istemcisi
// Tüm içerik admin panelinden (Filament) bu uçlar aracılığıyla çekilir.

// Bu modül yalnızca sunucu tarafı render sırasında çalışır.
// API_INTERNAL_URL tanımlıysa istekler genel alan adı yerine doğrudan
// origin'e gider; böylece sunucu kendi alan adını çözerken CDN ucuna çıkıp
// geri dönmez (bu gidiş-dönüş zaman zaman takılıyordu).
const API_BASE_URL =
  (typeof process !== 'undefined' ? process.env?.API_INTERNAL_URL : undefined) ||
  import.meta.env.PUBLIC_API_URL ||
  'http://kdbhukuk-admin.test/api';

/** Tek bir isteğin zaman aşımı. Yanıtsız kalan istek sayfayı süresiz askıda bırakmamalı. */
const API_TIMEOUT_MS = 5000;

async function apiRequest<T>(path: string): Promise<T | null> {
  const res = await fetch(`${API_BASE_URL}/${path}`, {
    signal: AbortSignal.timeout(API_TIMEOUT_MS),
  });

  if (!res.ok) return null;

  return (await res.json()) as T;
}

async function apiFetch<T>(path: string): Promise<T | null> {
  // Ağ hatası veya zaman aşımında bir kez daha denenir; kalıcı hatada sayfa
  // yedek metinlerle çizilir, istek askıda kalmaz.
  try {
    return await apiRequest<T>(path);
  } catch {
    try {
      return await apiRequest<T>(path);
    } catch {
      return null;
    }
  }
}

export interface SiteSettings {
  phone?: string;
  whatsapp?: string;
  email?: string;
  working_hours?: string;
  address?: string;
  instagram_url?: string;
  linkedin_url?: string;
}

export interface PageFields {
  [key: string]: any;
}

export interface PageData {
  slug: string;
  title: string;
  meta_title: string | null;
  meta_description: string | null;
  fields: PageFields;
}

export interface LegalPageData {
  slug: string;
  title: string;
  content: string;
  updated_at: string | null;
}

export interface ServiceFaq {
  question: string;
  answer: string;
}

export interface ServiceListItem {
  order: number;
  title: string;
  slug: string;
  short_description: string | null;
  cover_image: string | null;
}

export interface ServiceDetail extends ServiceListItem {
  description: string | null;
  covers: string | null;
  scope: string | null;
  process_steps: { title: string; description: string }[];
  why_important: string | null;
  meta_title: string | null;
  meta_description: string | null;
  faqs: ServiceFaq[];
}

export interface BlogCategory {
  name: string;
  slug: string;
}

export interface BlogPostListItem {
  title: string;
  slug: string;
  excerpt: string | null;
  cover_image: string | null;
  category: BlogCategory | null;
  reading_minutes: number | null;
  is_featured: boolean;
  published_at: string | null;
}

export interface BlogPostDetail extends Omit<BlogPostListItem, 'category'> {
  content: string;
  category: BlogCategory | null;
  author_name: string;
  meta_title: string | null;
  meta_description: string | null;
}

export async function getSiteSettings(): Promise<SiteSettings> {
  const res = await apiFetch<{ data: SiteSettings }>('site-settings');
  return res?.data ?? {};
}

export async function getPage(slug: string): Promise<PageData | null> {
  const res = await apiFetch<{ data: PageData }>(`pages/${slug}`);
  return res?.data ?? null;
}

export async function getLegalPage(slug: string): Promise<LegalPageData | null> {
  const res = await apiFetch<{ data: LegalPageData }>(`legal-pages/${slug}`);
  return res?.data ?? null;
}

export async function getServices(): Promise<ServiceListItem[]> {
  const res = await apiFetch<{ data: ServiceListItem[] }>('services');
  return res?.data ?? [];
}

export async function getService(slug: string): Promise<ServiceDetail | null> {
  const res = await apiFetch<{ data: ServiceDetail }>(`services/${slug}`);
  return res?.data ?? null;
}

export async function getBlogCategories(): Promise<BlogCategory[]> {
  const res = await apiFetch<{ data: BlogCategory[] }>('blog-categories');
  return res?.data ?? [];
}

export async function getBlogPosts(params?: { category?: string; per_page?: number }): Promise<BlogPostListItem[]> {
  const query = new URLSearchParams();
  if (params?.category) query.set('category', params.category);
  if (params?.per_page) query.set('per_page', String(params.per_page));
  const qs = query.toString();
  const res = await apiFetch<{ data: BlogPostListItem[] }>(`blog${qs ? `?${qs}` : ''}`);
  return res?.data ?? [];
}

export async function getBlogPost(slug: string): Promise<BlogPostDetail | null> {
  const res = await apiFetch<{ data: BlogPostDetail }>(`blog/${slug}`);
  return res?.data ?? null;
}

export interface TocItem {
  id: string;
  title: string;
}

function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/ı/g, 'i').replace(/ğ/g, 'g').replace(/ü/g, 'u')
    .replace(/ş/g, 's').replace(/ö/g, 'o').replace(/ç/g, 'c')
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Yasal sayfa içeriğindeki her <h2> başlığına (admin panelinden düzenlenmiş
 * olsa dahi, id özniteliği taşımasa bile) otomatik bir çapa kimliği ekler
 * ve içindekiler listesini bu kimliklerden üretir.
 */
export function prepareLegalContent(html: string): { html: string; toc: TocItem[] } {
  const toc: TocItem[] = [];
  const used = new Set<string>();

  const preparedHtml = html.replace(/<h2([^>]*)>(.*?)<\/h2>/gi, (full, attrs, inner) => {
    const plainTitle = inner.replace(/<[^>]+>/g, '').trim();
    const existingId = /\sid="([^"]+)"/.exec(attrs)?.[1];

    let id = existingId || slugify(plainTitle) || `bolum-${toc.length + 1}`;
    while (used.has(id)) id = `${id}-2`;
    used.add(id);

    toc.push({ id, title: plainTitle });

    const cleanAttrs = attrs.replace(/\sid="[^"]*"/, '');
    return `<h2${cleanAttrs} id="${id}">${inner}</h2>`;
  });

  return { html: preparedHtml, toc };
}

export function formatDateTr(iso: string | null): string {
  if (!iso) return '';
  const months = [
    'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
    'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık',
  ];
  const d = new Date(iso);
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}
