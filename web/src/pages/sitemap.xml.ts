import type { APIRoute } from 'astro';
import { getServices, getBlogPosts } from '../lib/api';

export const prerender = false;

const SITE_URL = 'https://www.kdbhukuk.com';

export const GET: APIRoute = async () => {
  const [services, posts] = await Promise.all([
    getServices(),
    getBlogPosts({ per_page: 200 }),
  ]);

  const staticUrls = [
    { loc: '/', priority: '1.0', changefreq: 'weekly' },
    { loc: '/hakkimizda', priority: '0.8', changefreq: 'monthly' },
    { loc: '/hizmetlerimiz', priority: '0.9', changefreq: 'monthly' },
    { loc: '/blog', priority: '0.8', changefreq: 'weekly' },
    { loc: '/iletisim', priority: '0.7', changefreq: 'yearly' },
    { loc: '/gizlilik-politikasi', priority: '0.3', changefreq: 'yearly' },
    { loc: '/cerez-politikasi', priority: '0.3', changefreq: 'yearly' },
    { loc: '/kvkk', priority: '0.3', changefreq: 'yearly' },
  ];

  const serviceUrls = services.map((s) => ({
    loc: `/hizmetlerimiz/${s.slug}`,
    priority: '0.8',
    changefreq: 'monthly',
  }));

  const blogUrls = posts.map((p) => ({
    loc: `/blog/${p.slug}`,
    priority: '0.6',
    changefreq: 'monthly',
  }));

  const allUrls = [...staticUrls, ...serviceUrls, ...blogUrls];

  const body = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${allUrls
  .map(
    (u) => `  <url>
    <loc>${SITE_URL}${u.loc}</loc>
    <priority>${u.priority}</priority>
    <changefreq>${u.changefreq}</changefreq>
  </url>`,
  )
  .join('\n')}
</urlset>
`;

  return new Response(body, {
    headers: { 'Content-Type': 'application/xml' },
  });
};
