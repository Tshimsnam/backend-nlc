import { useState, useEffect, lazy, Suspense } from "react";

const CertificatParticipation = lazy(() => import("./CertificatParticipation"));

const API_BASE = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

// ─── Types ────────────────────────────────────────────────────────────────────
type TsaQuestion = { id: number; text: string; options: string[] };

type FormData = {
  full_name: string; etablissement: string; profil: string; profil_autre: string;
  contact: string; date_colloque: string; duree_session: string;
  adequation_theme: string; aspects_pertinents: string; sujets_manquants: string;
  clarte_presentations: string; maintien_attention: string;
  organisation_generale: string; respect_horaires: string; logistique_commentaire: string;
  opportunites_interaction: string; contacts_professionnels: string;
  enseignements_tires: string; application_enseignements: string;
  note_globale: number | "";
  points_forts: string; suggestions_amelioration: string;
  commentaires_additionnels: string;
  tsa_answers: string[]; // index 0..N → tsa_q1..tsa_qN
};

const INIT: FormData = {
  full_name:"", etablissement:"", profil:"", profil_autre:"", contact:"",
  date_colloque:"", duree_session:"",
  adequation_theme:"", aspects_pertinents:"", sujets_manquants:"",
  clarte_presentations:"", maintien_attention:"",
  organisation_generale:"", respect_horaires:"", logistique_commentaire:"",
  opportunites_interaction:"", contacts_professionnels:"",
  enseignements_tires:"", application_enseignements:"",
  note_globale:"", points_forts:"", suggestions_amelioration:"",
  commentaires_additionnels:"",
  tsa_answers: [],
};

// ─── Helpers UI ───────────────────────────────────────────────────────────────
const Label = ({ children }: { children: React.ReactNode }) => (
  <p className="text-sm font-semibold text-gray-700 mb-2">{children}</p>
);

const TextArea = ({ value, onChange, placeholder }: {
  value: string; onChange: (v: string) => void; placeholder?: string;
}) => (
  <textarea value={value} onChange={e => onChange(e.target.value)}
    placeholder={placeholder ?? "Votre réponse…"} rows={3}
    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none" />
);

const RadioGroup = ({ options, value, onChange }: {
  options: { label: string; value: string }[];
  value: string; onChange: (v: string) => void;
}) => (
  <div className="space-y-2">
    {options.map(o => (
      <label key={o.value} className={`flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all ${value === o.value ? "border-purple-400 bg-purple-50" : "border-gray-100 hover:border-purple-200"}`}>
        <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${value === o.value ? "border-purple-500" : "border-gray-300"}`}>
          {value === o.value && <div className="w-2.5 h-2.5 rounded-full bg-purple-500" />}
        </div>
        <input type="radio" className="hidden" value={o.value} checked={value === o.value} onChange={() => onChange(o.value)} />
        <span className="text-sm text-gray-700">{o.label}</span>
      </label>
    ))}
  </div>
);

const SectionTitle = ({ num, title, subtitle }: { num: number; title: string; subtitle?: string }) => (
  <div className="mb-6">
    <div className="flex items-center gap-3 mb-1">
      <div className="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 text-white text-sm font-black flex items-center justify-center flex-shrink-0">{num}</div>
      <h2 className="text-lg font-black text-gray-800">{title}</h2>
    </div>
    {subtitle && <p className="text-xs text-gray-400 pl-11">{subtitle}</p>}
  </div>
);

const SECTIONS = [
  "Informations personnelles", "Contenu du colloque", "Qualité des présentations",
  "Organisation & logistique", "Interaction & Networking", "Retour sur l'apprentissage",
  "Évaluation globale", "Commentaires", "Quiz TSA",
];

// ─── Page principale ──────────────────────────────────────────────────────────
export default function ColloqueEvaluationPage() {
  const [form, setForm]           = useState<FormData>(INIT);
  const [section, setSection]     = useState(0);
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading]     = useState(false);
  const [error, setError]         = useState<string | null>(null);
  const [tsaQuestions, setTsaQuestions] = useState<TsaQuestion[]>([]);
  const [tsaLoading, setTsaLoading]     = useState(false);

  // Charger les questions TSA depuis l'API
  useEffect(() => {
    setTsaLoading(true);
    fetch(`${API_BASE}/colloque/questions`)
      .then(r => r.json())
      .then(d => {
        setTsaQuestions(d.questions ?? []);
        setForm(prev => ({ ...prev, tsa_answers: new Array(d.questions?.length ?? 0).fill("") }));
      })
      .catch(() => {/* silencieux — fallback vide */})
      .finally(() => setTsaLoading(false));
  }, []);

  const set = (key: keyof FormData) => (val: string | number) =>
    setForm(prev => ({ ...prev, [key]: val }));

  const setTsaAnswer = (index: number, val: string) =>
    setForm(prev => {
      const answers = [...prev.tsa_answers];
      answers[index] = val;
      return { ...prev, tsa_answers: answers };
    });

  const handleSubmit = async () => {
    setLoading(true); setError(null);
    try {
      const res = await fetch(`${API_BASE}/colloque/evaluate`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (!res.ok) { setError(data.message ?? "Erreur lors de l'envoi."); return; }
      setSubmitted(true);
    } catch {
      setError("Impossible de contacter le serveur.");
    } finally {
      setLoading(false);
    }
  };

  // ── Remerciement + certificat ─────────────────────────────────────────────
  if (submitted) {
    return (
      <Suspense fallback={<div className="min-h-screen flex items-center justify-center text-gray-400">Chargement…</div>}>
        <div className="min-h-screen bg-slate-100">
          {/* Message de remerciement */}
          <div className="text-center pt-10 pb-4 px-4">
            <div className="text-5xl mb-3">🎉</div>
            <h2 className="text-2xl font-bold text-gray-800 mb-1">Merci pour votre évaluation !</h2>
            <p className="text-gray-500 text-sm">
              Vos réponses ont été enregistrées. Téléchargez votre certificat de participation ci-dessous.
            </p>
          </div>
          {/* Certificat */}
          <CertificatParticipation
            fullName={form.full_name || "Participant(e)"}
            etablissement={form.etablissement}
            dateColloque={form.date_colloque}
            dureeSession={form.duree_session}
          />
        </div>
      </Suspense>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 py-8 px-4">
      <div className="max-w-2xl mx-auto">

        {/* Header */}
        <div className="text-center mb-8">
          <p className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-1">GSA 2026</p>
          <h1 className="text-3xl font-black text-gray-800 mb-2">Évaluation du Colloque</h1>
          <p className="text-gray-500 text-sm max-w-lg mx-auto leading-relaxed">
            Votre évaluation nous aidera à comprendre vos impressions et à améliorer nos futurs événements.
            Vos réponses seront traitées de manière confidentielle.
          </p>
        </div>

        {/* Stepper */}
        <div className="flex gap-1 mb-8 overflow-x-auto pb-2">
          {SECTIONS.map((s, i) => (
            <button key={i} onClick={() => setSection(i)}
              className={`flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold transition-all ${
                i === section ? "bg-purple-600 text-white shadow-md" : "bg-white text-gray-500 border border-gray-200 hover:border-purple-300"
              }`}>
              {i + 1}. {s.split(" ")[0]}
            </button>
          ))}
        </div>

        {/* Card */}
        <div className="bg-white rounded-3xl shadow-sm border border-purple-100 p-6 mb-6">

          {section === 0 && (
            <>
              <SectionTitle num={1} title="Informations personnelles" />
              <div className="space-y-4">
                <div><Label>Nom complet</Label>
                  <input value={form.full_name} onChange={e => set("full_name")(e.target.value)} placeholder="Votre nom et prénom"
                    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" /></div>
                <div><Label>Établissement</Label>
                  <input value={form.etablissement} onChange={e => set("etablissement")(e.target.value)} placeholder="Nom de votre établissement"
                    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" /></div>
                <div>
                  <Label>Je suis</Label>
                  <div className="grid grid-cols-2 gap-2">
                    {["Étudiant(e)","Docteur(e)","Professeur(e)","Parent","Enseignant(e)","Autiste","Autre"].map(p => (
                      <label key={p} className={`flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer text-sm transition-all ${form.profil === p ? "border-purple-400 bg-purple-50 font-semibold text-purple-700" : "border-gray-100 text-gray-600 hover:border-purple-200"}`}>
                        <input type="radio" className="hidden" checked={form.profil === p} onChange={() => set("profil")(p)} />
                        <div className={`w-4 h-4 rounded-full border-2 flex-shrink-0 ${form.profil === p ? "border-purple-500 bg-purple-500" : "border-gray-300"}`} />
                        {p}
                      </label>
                    ))}
                  </div>
                  {form.profil === "Autre" && (
                    <input value={form.profil_autre} onChange={e => set("profil_autre")(e.target.value)} placeholder="Précisez…"
                      className="mt-2 w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />
                  )}
                </div>
                <div><Label>Contact</Label>
                  <input value={form.contact} onChange={e => set("contact")(e.target.value)} placeholder="Téléphone ou email"
                    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" /></div>
                <div className="grid grid-cols-2 gap-4">
                  <div><Label>Date du colloque</Label>
                    <input type="date" value={form.date_colloque} onChange={e => set("date_colloque")(e.target.value)}
                      className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" /></div>
                  <div><Label>Durée de la session</Label>
                    <input value={form.duree_session} onChange={e => set("duree_session")(e.target.value)} placeholder="Ex: 3h"
                      className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" /></div>
                </div>
              </div>
            </>
          )}

          {section === 1 && (
            <>
              <SectionTitle num={2} title="Contenu du colloque" />
              <div className="space-y-6">
                <div><Label>Le thème du colloque était-il en adéquation avec vos attentes ?</Label>
                  <RadioGroup value={form.adequation_theme} onChange={set("adequation_theme")} options={[
                    {label:"Très en adéquation",value:"tres_adequat"},{label:"En adéquation",value:"adequat"},
                    {label:"Neutre",value:"neutre"},{label:"Pas vraiment en adéquation",value:"pas_vraiment"},
                    {label:"Pas du tout en adéquation",value:"pas_du_tout"},
                  ]} /></div>
                <div><Label>Quels aspects du contenu avez-vous trouvés les plus pertinents et pourquoi ?</Label>
                  <TextArea value={form.aspects_pertinents} onChange={set("aspects_pertinents")} /></div>
                <div><Label>Y a-t-il des sujets que vous auriez souhaité voir abordés ?</Label>
                  <TextArea value={form.sujets_manquants} onChange={set("sujets_manquants")} /></div>
              </div>
            </>
          )}

          {section === 2 && (
            <>
              <SectionTitle num={3} title="Qualité des présentations" />
              <div className="space-y-6">
                <div><Label>Comment évaluez-vous la clarté et la pertinence des présentations ?</Label>
                  <RadioGroup value={form.clarte_presentations} onChange={set("clarte_presentations")} options={[
                    {label:"Excellente",value:"excellente"},{label:"Très bonne",value:"tres_bonne"},
                    {label:"Bonne",value:"bonne"},{label:"Acceptable",value:"acceptable"},{label:"Insatisfaisante",value:"insatisfaisante"},
                  ]} /></div>
                <div><Label>Les intervenants ont-ils réussi à maintenir votre attention ?</Label>
                  <RadioGroup value={form.maintien_attention} onChange={set("maintien_attention")} options={[
                    {label:"Toujours",value:"toujours"},{label:"Souvent",value:"souvent"},
                    {label:"Parfois",value:"parfois"},{label:"Jamais",value:"jamais"},
                  ]} /></div>
              </div>
            </>
          )}

          {section === 3 && (
            <>
              <SectionTitle num={4} title="Organisation & logistique" />
              <div className="space-y-6">
                <div><Label>Comment évaluez-vous l'organisation générale ?</Label>
                  <RadioGroup value={form.organisation_generale} onChange={set("organisation_generale")} options={[
                    {label:"Excellente",value:"excellente"},{label:"Très bonne",value:"tres_bonne"},
                    {label:"Bonne",value:"bonne"},{label:"Acceptable",value:"acceptable"},{label:"À améliorer",value:"a_ameliorer"},
                  ]} /></div>
                <div><Label>Les horaires ont-ils été respectés ?</Label>
                  <RadioGroup value={form.respect_horaires} onChange={set("respect_horaires")} options={[
                    {label:"Toujours",value:"toujours"},{label:"La plupart du temps",value:"la_plupart"},
                    {label:"Parfois",value:"parfois"},{label:"Rarement",value:"rarement"},{label:"Jamais",value:"jamais"},
                  ]} /></div>
                <div><Label>Commentaires sur la logistique (accueil, matériel, pauses…)</Label>
                  <TextArea value={form.logistique_commentaire} onChange={set("logistique_commentaire")} /></div>
              </div>
            </>
          )}

          {section === 4 && (
            <>
              <SectionTitle num={5} title="Interaction & Networking" />
              <div className="space-y-6">
                <div><Label>Avez-vous eu l'opportunité d'interagir avec les autres participants ?</Label>
                  <RadioGroup value={form.opportunites_interaction} onChange={set("opportunites_interaction")} options={[
                    {label:"Beaucoup d'opportunités",value:"beaucoup"},{label:"Quelques opportunités",value:"quelques"},
                    {label:"Peu d'opportunités",value:"peu"},{label:"Aucune opportunité",value:"aucune"},
                  ]} /></div>
                <div><Label>Avez-vous établi des contacts professionnels utiles ?</Label>
                  <TextArea value={form.contacts_professionnels} onChange={set("contacts_professionnels")} placeholder="Décrivez vos échanges…" /></div>
              </div>
            </>
          )}

          {section === 5 && (
            <>
              <SectionTitle num={6} title="Retour sur l'apprentissage" />
              <div className="space-y-6">
                <div><Label>Quels sont les principaux enseignements que vous avez tirés ?</Label>
                  <TextArea value={form.enseignements_tires} onChange={set("enseignements_tires")} /></div>
                <div><Label>Comment prévoyez-vous appliquer ces enseignements ?</Label>
                  <TextArea value={form.application_enseignements} onChange={set("application_enseignements")} /></div>
              </div>
            </>
          )}

          {section === 6 && (
            <>
              <SectionTitle num={7} title="Évaluation globale" />
              <div className="space-y-6">
                <div>
                  <Label>Note globale (1 à 10)</Label>
                  <div className="flex gap-2 flex-wrap">
                    {[1,2,3,4,5,6,7,8,9,10].map(n => (
                      <button key={n} onClick={() => set("note_globale")(n)}
                        className={`w-10 h-10 rounded-xl font-bold text-sm transition-all ${form.note_globale === n ? "bg-gradient-to-br from-purple-500 to-blue-500 text-white shadow-md" : "bg-gray-100 text-gray-600 hover:bg-purple-100"}`}>
                        {n}
                      </button>
                    ))}
                  </div>
                </div>
                <div><Label>Quels sont les points forts du colloque ?</Label>
                  <TextArea value={form.points_forts} onChange={set("points_forts")} /></div>
                <div><Label>Comment pourrions-nous améliorer ce colloque ?</Label>
                  <TextArea value={form.suggestions_amelioration} onChange={set("suggestions_amelioration")} /></div>
              </div>
            </>
          )}

          {section === 7 && (
            <>
              <SectionTitle num={8} title="Commentaires additionnels" />
              <div><Label>Autres commentaires, suggestions ou remarques</Label>
                <TextArea value={form.commentaires_additionnels} onChange={set("commentaires_additionnels")} placeholder="Tout ce que vous souhaitez partager…" /></div>
            </>
          )}

          {/* ── Section 9 — Questions TSA dynamiques ── */}
          {section === 8 && (
            <>
              <SectionTitle num={9} title="Quiz TSA"
                subtitle="Questionnaire d'évaluation des connaissances sur les Troubles du Spectre de l'Autisme" />
              {tsaLoading ? (
                <div className="text-center py-8 text-gray-400 text-sm">Chargement des questions…</div>
              ) : tsaQuestions.length === 0 ? (
                <div className="text-center py-8 text-gray-400 text-sm italic">Aucune question disponible pour le moment.</div>
              ) : (
                <div className="space-y-6">
                  {tsaQuestions.map((q, i) => (
                    <div key={q.id}>
                      <Label>{i + 1}. {q.text}</Label>
                      <RadioGroup
                        value={form.tsa_answers[i] ?? ""}
                        onChange={val => setTsaAnswer(i, val)}
                        options={q.options.map((opt, j) => ({
                          label: opt,
                          value: String.fromCharCode(65 + j), // A, B, C, D…
                        }))}
                      />
                    </div>
                  ))}
                </div>
              )}
            </>
          )}
        </div>

        {error && (
          <div className="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">{error}</div>
        )}

        {/* Navigation */}
        <div className="flex justify-between items-center">
          <button onClick={() => setSection(s => Math.max(0, s - 1))} disabled={section === 0}
            className="px-6 py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:border-purple-300 disabled:opacity-30 transition-all">
            ← Précédent
          </button>
          <span className="text-xs text-gray-400">{section + 1} / {SECTIONS.length}</span>
          {section < SECTIONS.length - 1 ? (
            <button onClick={() => setSection(s => s + 1)}
              className="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold text-sm hover:opacity-90 transition-all shadow-md">
              Suivant →
            </button>
          ) : (
            <button onClick={handleSubmit} disabled={loading}
              className="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold text-sm hover:opacity-90 disabled:opacity-60 transition-all shadow-md">
              {loading ? "Envoi…" : "Soumettre ✓"}
            </button>
          )}
        </div>

        <p className="text-center text-xs text-gray-400 mt-4">
          🔒 Vos réponses sont traitées de manière confidentielle — Never Limit Children
        </p>
      </div>
    </div>
  );
}
