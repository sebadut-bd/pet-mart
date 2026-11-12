import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { Inbox, ArrowUpRight, ArrowDownLeft } from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import { useDocumentTitle } from '../hooks/useDocumentTitle';
import { AccountLayout } from '../components/layout/AccountLayout';
import { EmptyState } from '../components/ui/EmptyState';
import { Skeleton } from '../components/ui/Skeleton';
import { formatDate, formatRelativeTime, initials } from '../lib/format';

export default function Inquiries() {
  useDocumentTitle('Inquiries');
  const { user } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    setError(false);
    api
      .get('/inquiries')
      .then((res) => setItems(res.data?.data || []))
      .catch(() => setError(true))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  // Group messages by listing, newest conversation first
  const groups = useMemo(() => {
    const map = new Map();
    items.forEach((m) => {
      const key = m.product_id ?? 'unknown';
      if (!map.has(key)) map.set(key, { productId: m.product_id, title: m.product_title || 'Deleted listing', messages: [] });
      map.get(key).messages.push(m);
    });
    return [...map.values()].map((g) => ({
      ...g,
      messages: g.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at)),
      latest: g.messages.reduce((t, m) => Math.max(t, new Date(m.created_at).getTime()), 0),
      unread: g.messages.filter((m) => !m.is_read && m.receiver_name === user?.name).length,
    })).sort((a, b) => b.latest - a.latest);
  }, [items, user?.name]);

  return (
    <AccountLayout title="Inquiries" subtitle="Messages between you and other members about listings.">
      {loading ? (
        <div className="space-y-3">
          {Array.from({ length: 3 }).map((_, i) => (
            <Skeleton key={i} className="h-32 w-full rounded-2xl" />
          ))}
        </div>
      ) : error ? (
        <div className="rounded-2xl border border-border bg-white">
          <EmptyState icon={Inbox} title="Messages could not be loaded" action={load} actionLabel="Try again" />
        </div>
      ) : groups.length === 0 ? (
        <div className="rounded-2xl border border-border bg-white">
          <EmptyState
            icon={Inbox}
            title="No messages yet"
            description="When you message a seller, or a buyer messages you, the conversation shows up here."
            actionTo="/browse"
            actionLabel="Browse listings"
          />
        </div>
      ) : (
        <div className="space-y-4">
          {groups.map((g) => (
            <section
              key={g.productId ?? g.title}
              className={`overflow-hidden rounded-2xl border bg-white ${g.unread ? 'border-primary/40 ring-1 ring-primary/20' : 'border-border'}`}
              aria-label={`Conversation about ${g.title}`}
            >
              <header className="flex flex-wrap items-center justify-between gap-2 border-b border-border bg-slate-50 px-4 py-3">
                <div className="min-w-0">
                  {g.productId ? (
                    <Link to={`/product/${g.productId}`} className="truncate font-bold text-navy hover:text-primary-hover hover:underline">
                      {g.title}
                    </Link>
                  ) : (
                    <span className="font-bold text-navy">{g.title}</span>
                  )}
                  <p className="text-xs text-muted">
                    {g.messages.length} message{g.messages.length !== 1 ? 's' : ''} · last {formatRelativeTime(new Date(g.latest).toISOString())}
                  </p>
                </div>
                {g.unread > 0 && (
                  <span className="rounded-full bg-primary px-2.5 py-0.5 text-xs font-bold text-white">{g.unread} new</span>
                )}
              </header>
              <ol className="space-y-3 p-4">
                {g.messages.map((m) => {
                  const mine = m.sender_name === user?.name;
                  return (
                    <li key={m.id} className={`flex gap-3 ${mine ? 'flex-row-reverse' : ''}`}>
                      <span
                        className={`grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold ${
                          mine ? 'bg-primary text-white' : 'bg-slate-200 text-slate-700'
                        }`}
                        aria-hidden="true"
                      >
                        {initials(m.sender_name)}
                      </span>
                      <div className={`max-w-[85%] sm:max-w-[70%] ${mine ? 'text-right' : ''}`}>
                        <p className="mb-1 flex items-center gap-1 text-xs text-muted">
                          {mine ? <ArrowUpRight size={12} aria-hidden="true" /> : <ArrowDownLeft size={12} aria-hidden="true" />}
                          {mine ? `You → ${m.receiver_name}` : `${m.sender_name} → you`}
                          <time dateTime={m.created_at} title={formatDate(m.created_at)} className="ml-1">
                            · {formatRelativeTime(m.created_at)}
                          </time>
                        </p>
                        <p
                          className={`inline-block whitespace-pre-line break-words rounded-2xl px-4 py-2.5 text-left text-sm ${
                            mine ? 'rounded-tr-sm bg-primary/10 text-navy' : 'rounded-tl-sm bg-slate-100 text-slate-800'
                          } ${!m.is_read && !mine ? 'font-semibold' : ''}`}
                        >
                          {m.message}
                        </p>
                      </div>
                    </li>
                  );
                })}
              </ol>
            </section>
          ))}
          <p className="text-center text-xs text-muted">
            Replying from this page is not available yet.
          </p>
        </div>
      )}
    </AccountLayout>
  );
}
