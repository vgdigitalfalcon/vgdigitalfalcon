import React, { useState, useMemo } from 'react';
import {
  TrendingUp,
  Layers,
  ShieldCheck,
  CalendarCheck,
  Clapperboard,
  FileText,
  UserCheck,
  BarChart3,
  Filter,
  Megaphone,
  ArrowRight,
  Sparkles,
  Bot,
  Zap,
  Activity,
  CheckCircle2,
  LucideIcon,
} from 'lucide-react';

export type AgentCategory = 'SEO' | 'Content' | 'Social' | 'Growth & Ads';
export type FilterTab = 'All' | AgentCategory;

export interface AgentItem {
  id: string;
  name: string;
  category: AgentCategory;
  icon: string;
  desc: string;
  stat?: string;
}

// Icon mapping dictionary
const ICON_MAP: Record<string, LucideIcon> = {
  TrendingUp,
  Layers,
  ShieldCheck,
  CalendarCheck,
  Clapperboard,
  FileText,
  UserCheck,
  BarChart3,
  Filter,
  Megaphone,
};

// Agents Data Array
export const AGENTS_DATA: AgentItem[] = [
  {
    id: 'rank',
    name: 'RankFalcon',
    category: 'SEO',
    icon: 'TrendingUp',
    desc: 'AI-powered rank tracking and SERP intelligence that keeps you ahead.',
    stat: '99.4% Accuracy',
  },
  {
    id: 'gap',
    name: 'GapFalcon',
    category: 'SEO',
    icon: 'Layers',
    desc: 'Uncovers content and keyword gaps to find opportunites others miss.',
    stat: '10x Fast Discovery',
  },
  {
    id: 'audit',
    name: 'AuditFalcon',
    category: 'SEO',
    icon: 'ShieldCheck',
    desc: 'Comprehensive technical SEO audits that find issues and drive results.',
    stat: '140+ Audit Checks',
  },
  {
    id: 'post',
    name: 'PostFalcon',
    category: 'Content',
    icon: 'CalendarCheck',
    desc: 'AI content planning and optimization that ranks and engages.',
    stat: 'Auto-Scheduling',
  },
  {
    id: 'clip',
    name: 'ClipFalcon',
    category: 'Social',
    icon: 'Clapperboard',
    desc: 'Turns long-form content into short, viral-ready clips in seconds.',
    stat: '< 60s Generation',
  },
  {
    id: 'script',
    name: 'ScriptFalcon',
    category: 'Content',
    icon: 'FileText',
    desc: 'AI scriptwriting that crafts persuasive, high-converting scripts.',
    stat: 'High-Retention',
  },
  {
    id: 'profile',
    name: 'ProfileFalcon',
    category: 'Social',
    icon: 'UserCheck',
    desc: 'Optimizes GMB and social profiles to boost visibility and trust.',
    stat: '+48% Profile CTR',
  },
  {
    id: 'report',
    name: 'ReportFalcon',
    category: 'Growth & Ads',
    icon: 'BarChart3',
    desc: 'Automated, insightful reports that prove value and track growth.',
    stat: 'Live Telemetry',
  },
  {
    id: 'convert',
    name: 'ConvertFalcon',
    category: 'Growth & Ads',
    icon: 'Filter',
    desc: 'AI lead conversion and nurturing that turns traffic into customers.',
    stat: '3.8x Funnel Lift',
  },
  {
    id: 'ads',
    name: 'AdsFalcon',
    category: 'Growth & Ads',
    icon: 'Megaphone',
    desc: 'AI-powered ad strategy and optimization that scales profitably.',
    stat: 'Autonomous ROAS',
  },
];

export const CATEGORIES: FilterTab[] = ['All', 'SEO', 'Content', 'Social', 'Growth & Ads'];

/**
 * Animated Standing AI Sentinel Mascot SVG
 */
export const StandingRoboSentinel: React.FC = () => {
  return (
    <div className="relative flex flex-col items-center justify-center select-none py-2">
      {/* Ambient Back Glow */}
      <div className="absolute -inset-4 bg-gradient-to-r from-purple-500/20 via-purple-600/25 to-cyan-500/20 blur-3xl rounded-full pointer-events-none" />

      {/* Live Badge over Sentinel */}
      <div className="mb-3 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/80 border border-purple-500/40 text-[11px] font-mono text-purple-300 shadow-[0_0_15px_rgba(20,184,166,0.3)] backdrop-blur-md">
        <span className="relative flex h-2 w-2">
          <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75" />
          <span className="relative inline-flex rounded-full h-2 w-2 bg-purple-400" />
        </span>
        <span className="font-semibold tracking-wider">FALCON AI SENTINEL</span>
        <span className="text-purple-500">|</span>
        <span className="text-slate-400">AUTONOMOUS</span>
      </div>

      {/* Standing Robo SVG */}
      <div className="relative w-44 h-56 sm:w-52 sm:h-64 animate-[falconRoboFloat_4s_ease-in-out_infinite]">
        <svg
          viewBox="0 0 200 240"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          className="w-full h-full drop-shadow-[0_12px_24px_rgba(0,0,0,0.6)]"
        >
          <defs>
            <linearGradient id="tsArmorGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stopColor="#1e1b4b" />
              <stop offset="50%" stopColor="#0f172a" />
              <stop offset="100%" stopColor="#020617" />
            </linearGradient>

            <linearGradient id="tspurpleMetal" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stopColor="#c084fc" />
              <stop offset="50%" stopColor="#7c3aed" />
              <stop offset="100%" stopColor="#581c87" />
            </linearGradient>

            <linearGradient id="tsVisorGrad" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#06b6d4" />
              <stop offset="50%" stopColor="#c084fc" />
              <stop offset="100%" stopColor="#38bdf8" />
            </linearGradient>

            <linearGradient id="tsCoreGlow" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor="#f5f3ff" />
              <stop offset="100%" stopColor="#0f766e" />
            </linearGradient>

            <linearGradient id="tsPedestalBeam" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor="#a855f7" stopOpacity="0.4" />
              <stop offset="100%" stopColor="#a855f7" stopOpacity="0" />
            </linearGradient>

            <filter id="tsNeonpurpleGlow" x="-30%" y="-30%" width="160%" height="160%">
              <feGaussianBlur stdDeviation="3.5" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>

            <filter id="tsIntenseCoreGlow" x="-50%" y="-50%" width="200%" height="200%">
              <feGaussianBlur stdDeviation="5" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
          </defs>

          {/* Hologram Light Cone & Pedestal Beam */}
          <polygon
            points="65,200 135,200 170,230 30,230"
            fill="url(#tsPedestalBeam)"
            opacity="0.6"
          />

          {/* Projected Holographic Rings at Base */}
          <ellipse
            cx="100"
            cy="225"
            rx="65"
            ry="12"
            stroke="#a855f7"
            strokeWidth="1.5"
            strokeDasharray="6 4"
            opacity="0.8"
            className="animate-[spin_12s_linear_infinite]"
          />
          <ellipse
            cx="100"
            cy="225"
            rx="48"
            ry="9"
            stroke="#38bdf8"
            strokeWidth="1.8"
            fill="none"
            filter="url(#tsNeonpurpleGlow)"
            opacity="0.75"
          />
          <ellipse
            cx="100"
            cy="225"
            rx="25"
            ry="5"
            fill="#a855f7"
            opacity="0.4"
            filter="url(#tsIntenseCoreGlow)"
          />

          {/* Mechanical Legs & Grounded Boots */}
          <g id="tsRoboLegs">
            <rect x="74" y="145" width="18" height="42" rx="4" fill="url(#tsArmorGrad)" stroke="#a855f7" strokeWidth="1.2" />
            <rect x="76" y="155" width="14" height="20" rx="2" fill="#0f172a" />
            <path d="M 72 187 L 94 187 L 96 205 L 68 205 Z" fill="url(#tsArmorGrad)" stroke="#c084fc" strokeWidth="1.2" />
            <rect x="72" y="202" width="24" height="4" rx="2" fill="#a855f7" filter="url(#tsNeonpurpleGlow)" />

            <rect x="108" y="145" width="18" height="42" rx="4" fill="url(#tsArmorGrad)" stroke="#a855f7" strokeWidth="1.2" />
            <rect x="110" y="155" width="14" height="20" rx="2" fill="#0f172a" />
            <path d="M 106 187 L 128 187 L 132 205 L 104 205 Z" fill="url(#tsArmorGrad)" stroke="#c084fc" strokeWidth="1.2" />
            <rect x="104" y="202" width="28" height="4" rx="2" fill="#a855f7" filter="url(#tsNeonpurpleGlow)" />
          </g>

          {/* Pelvis & Hip Joint */}
          <path
            d="M 68 138 L 132 138 L 124 152 L 76 152 Z"
            fill="url(#tsArmorGrad)"
            stroke="#a855f7"
            strokeWidth="1.2"
          />
          <circle cx="100" cy="145" r="4" fill="#c084fc" filter="url(#tsNeonpurpleGlow)" />

          {/* Torso & Chiseled Chest Armor */}
          <g id="tsRoboTorso">
            <path
              d="M 62 82 L 138 82 L 126 138 L 74 138 Z"
              fill="url(#tsArmorGrad)"
              stroke="#7c3aed"
              strokeWidth="1.5"
            />
            <path d="M 68 96 L 76 114" stroke="#c084fc" strokeWidth="1.5" strokeLinecap="round" />
            <path d="M 132 96 L 124 114" stroke="#c084fc" strokeWidth="1.5" strokeLinecap="round" />

            {/* Glowing Arc Reactor Chest Core */}
            <circle
              cx="100"
              cy="104"
              r="14"
              fill="#1e0b36"
              stroke="#c084fc"
              strokeWidth="2"
            />
            <circle
              cx="100"
              cy="104"
              r="9"
              fill="url(#tsCoreGlow)"
              filter="url(#tsIntenseCoreGlow)"
              className="animate-[pulse_2s_ease-in-out_infinite]"
            />
            <polygon
              points="100,98 106,108 94,108"
              fill="#ffffff"
              opacity="0.95"
            />
          </g>

          {/* Cybernetic Arms & Shoulders */}
          <g id="tsRoboArms">
            <rect x="42" y="80" width="22" height="24" rx="6" fill="url(#tsArmorGrad)" stroke="#a855f7" strokeWidth="1.2" />
            <path d="M 45 88 L 61 88" stroke="#c084fc" strokeWidth="1.5" filter="url(#tsNeonpurpleGlow)" />
            <rect x="46" y="104" width="14" height="28" rx="4" fill="url(#tsArmorGrad)" stroke="#7c3aed" strokeWidth="1" />
            <path d="M 44 130 L 58 130 L 60 152 L 46 152 Z" fill="url(#tsArmorGrad)" stroke="#c084fc" strokeWidth="1.2" />
            <circle cx="53" cy="156" r="5" fill="#0f172a" stroke="#a855f7" strokeWidth="1" />

            <rect x="136" y="80" width="22" height="24" rx="6" fill="url(#tsArmorGrad)" stroke="#a855f7" strokeWidth="1.2" />
            <path d="M 139 88 L 155 88" stroke="#c084fc" strokeWidth="1.5" filter="url(#tsNeonpurpleGlow)" />
            <rect x="140" y="104" width="14" height="28" rx="4" fill="url(#tsArmorGrad)" stroke="#7c3aed" strokeWidth="1" />
            <path d="M 138 130 L 152 130 L 150 152 L 136 152 Z" fill="url(#tsArmorGrad)" stroke="#c084fc" strokeWidth="1.2" />
            <circle cx="144" cy="156" r="5" fill="#0f172a" stroke="#a855f7" strokeWidth="1" />
          </g>

          {/* Head & Futuristic Helmet */}
          <g id="tsRoboHead">
            <rect x="90" y="72" width="20" height="12" rx="2" fill="#0f172a" stroke="#a855f7" strokeWidth="1" />

            <path d="M 72 40 L 76 56 L 68 56 Z" fill="url(#tspurpleMetal)" filter="url(#tsNeonpurpleGlow)" />
            <path d="M 128 40 L 132 56 L 124 56 Z" fill="url(#tspurpleMetal)" filter="url(#tsNeonpurpleGlow)" />

            <path
              d="M 70 44 C 70 32, 130 32, 130 44 L 134 72 L 66 72 Z"
              fill="url(#tsArmorGrad)"
              stroke="#c084fc"
              strokeWidth="1.6"
            />

            <path
              d="M 94 32 L 106 32 L 104 46 L 96 46 Z"
              fill="url(#tspurpleMetal)"
            />

            <rect
              x="74"
              y="50"
              width="52"
              height="16"
              rx="4"
              fill="#14082c"
              stroke="#c084fc"
              strokeWidth="1.5"
            />
            <rect
              x="76"
              y="52"
              width="48"
              height="12"
              rx="3"
              fill="url(#tsVisorGrad)"
              filter="url(#tsNeonpurpleGlow)"
              className="animate-[pulse_1.5s_ease-in-out_infinite]"
            />

            <rect
              x="92"
              y="54"
              width="16"
              height="8"
              rx="2"
              fill="#ffffff"
              opacity="0.9"
              className="animate-[falconVisorScan_2.4s_ease-in-out_infinite]"
            />
          </g>

          <circle cx="34" cy="74" r="2" fill="#c084fc" opacity="0.8" className="animate-[ping_3s_ease-in-out_infinite]" />
          <circle cx="168" cy="110" r="2.5" fill="#38bdf8" opacity="0.75" className="animate-[ping_2.5s_ease-in-out_infinite]" />
          <circle cx="48" cy="170" r="1.5" fill="#a855f7" opacity="0.9" />
          <circle cx="158" cy="180" r="2" fill="#c084fc" opacity="0.85" />
        </svg>
      </div>

      <div className="mt-2 text-center">
        <span className="text-[12px] font-mono tracking-wider text-purple-400 font-semibold flex items-center justify-center gap-1.5">
          <Sparkles className="w-3.5 h-3.5 text-purple-300 animate-spin" />
          READY TO DEPLOY
        </span>
      </div>
    </div>
  );
};

export const FalconAgentsSection: React.FC = () => {
  const [activeCategory, setActiveCategory] = useState<FilterTab>('All');

  const filteredAgents = useMemo(() => {
    if (activeCategory === 'All') return AGENTS_DATA;
    return AGENTS_DATA.filter((agent) => agent.category === activeCategory);
  }, [activeCategory]);

  return (
    <section
      id="falcon-agents-section"
      className="relative w-full bg-[#070212] text-white py-20 px-4 sm:px-6 lg:px-8 overflow-hidden font-sans"
    >
      {/* Background Ambient Glow Gradients (Matching VG Digital Theme) */}
      <div className="absolute top-0 left-1/4 w-96 h-96 bg-purple-600/15 rounded-full blur-[120px] pointer-events-none" />
      <div className="absolute top-1/3 right-1/4 w-[28rem] h-[28rem] bg-purple-500/15 rounded-full blur-[140px] pointer-events-none" />
      <div className="absolute bottom-0 left-1/3 w-96 h-96 bg-cyan-600/10 rounded-full blur-[120px] pointer-events-none" />

      {/* Subtle Dot Matrix Ambient Texture */}
      <div
        className="absolute inset-0 opacity-[0.07] pointer-events-none"
        style={{
          backgroundImage: 'radial-gradient(rgba(45, 212, 191, 0.4) 1px, transparent 1px)',
          backgroundSize: '28px 28px',
        }}
      />

      <div className="relative max-w-7xl mx-auto z-10">
        {/* Top Header Grid: Title + Standing Robo Mascot */}
        <div className="flex flex-col lg:flex-row items-center justify-between gap-8 mb-12">
          {/* Left Column: Heading & Subheading */}
          <div className="text-center lg:text-left max-w-2xl">
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-300 text-xs font-semibold tracking-wider uppercase mb-4 shadow-[0_0_15px_rgba(20,184,166,0.15)]">
              <Bot className="w-4 h-4 text-purple-400" />
              <span>Autonomous Growth Infrastructure</span>
            </div>

            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white mb-4 leading-tight">
              Meet the{' '}
              <span className="bg-gradient-to-r from-purple-400 via-cyan-400 to-purple-400 bg-clip-text text-transparent">
                Falcon Agents
              </span>
            </h2>

            <p className="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl">
              Our proprietary AI suite that powers every campaign with real-time intelligence, automated execution, and measurable ROI.
            </p>

            <div className="mt-6 flex flex-wrap items-center justify-center lg:justify-start gap-4 text-xs text-slate-400 font-mono">
              <div className="flex items-center gap-1.5 bg-slate-900/60 border border-slate-800 px-3 py-1.5 rounded-lg">
                <CheckCircle2 className="w-3.5 h-3.5 text-purple-400" />
                <span>10 Specialized Agents</span>
              </div>
              <div className="flex items-center gap-1.5 bg-slate-900/60 border border-slate-800 px-3 py-1.5 rounded-lg">
                <Zap className="w-3.5 h-3.5 text-cyan-400" />
                <span>24/7 Continuous Execution</span>
              </div>
              <div className="flex items-center gap-1.5 bg-slate-900/60 border border-slate-800 px-3 py-1.5 rounded-lg">
                <Activity className="w-3.5 h-3.5 text-purple-400" />
                <span>Zero Manual Guesswork</span>
              </div>
            </div>
          </div>

          {/* Right Column: Standing Robo Animation */}
          <div className="flex-shrink-0">
            <StandingRoboSentinel />
          </div>
        </div>

        {/* Feature 1: Category Filter Tabs */}
        <div className="flex items-center justify-center mb-10">
          <div className="inline-flex flex-wrap items-center justify-center gap-2 p-1.5 rounded-2xl bg-slate-900/80 border border-purple-500/20 backdrop-blur-xl shadow-xl">
            {CATEGORIES.map((cat) => {
              const isActive = activeCategory === cat;
              const count =
                cat === 'All'
                  ? AGENTS_DATA.length
                  : AGENTS_DATA.filter((a) => a.category === cat).length;

              return (
                <button
                  key={cat}
                  type="button"
                  onClick={() => setActiveCategory(cat)}
                  className={`relative px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-300 flex items-center gap-2 cursor-pointer ${
                    isActive
                      ? 'bg-gradient-to-r from-purple-600 to-purple-500 text-white shadow-lg shadow-purple-500/30 border border-purple-400/50 scale-[1.02]'
                      : 'text-slate-400 hover:text-white hover:bg-slate-800/60 border border-transparent'
                  }`}
                >
                  <span>{cat}</span>
                  <span
                    className={`text-[11px] font-mono px-1.5 py-0.5 rounded-full transition-colors ${
                      isActive
                        ? 'bg-purple-900/80 text-purple-200'
                        : 'bg-slate-800 text-slate-400 group-hover:text-slate-200'
                    }`}
                  >
                    {count}
                  </span>
                </button>
              );
            })}
          </div>
        </div>

        {/* Feature 2: Responsive Agent Cards Grid (5 cols on xl desktop for 2 compact rows) */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
          {filteredAgents.map((agent) => {
            const IconComponent = ICON_MAP[agent.icon] || Bot;

            return (
              <div
                key={agent.id}
                className="group relative rounded-2xl p-6 bg-gradient-to-b from-[#130b2c]/85 to-[#0b051c]/95 border border-purple-500/20 hover:border-purple-400/60 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_16px_36px_rgba(20,184,166,0.18)] flex flex-col justify-between"
              >
                <div className="absolute inset-0 rounded-2xl bg-gradient-to-br from-purple-500/10 via-transparent to-purple-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" />

                <div>
                  <div className="relative flex items-center justify-between mb-5 z-10">
                    <div className="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400 group-hover:bg-purple-500/20 group-hover:border-purple-400/60 group-hover:text-purple-300 group-hover:scale-110 group-hover:shadow-[0_0_15px_rgba(45,212,191,0.35)] transition-all duration-300">
                      <IconComponent className="w-6 h-6 stroke-[1.8]" />
                    </div>

                    <span className="text-[11px] font-mono font-medium px-2.5 py-1 rounded-full bg-slate-900/80 border border-slate-700/80 text-slate-300 group-hover:border-purple-500/40 group-hover:text-purple-300 transition-colors">
                      {agent.category}
                    </span>
                  </div>

                  <h3 className="relative text-lg font-bold text-white group-hover:text-purple-200 transition-colors mb-2 tracking-tight z-10 flex items-center gap-1.5">
                    <span>{agent.name}</span>
                    <span className="opacity-0 group-hover:opacity-100 transition-opacity text-purple-400 text-xs">
                      ⚡
                    </span>
                  </h3>

                  <p className="relative text-sm text-slate-300 line-clamp-2 leading-relaxed mb-4 z-10">
                    {agent.desc}
                  </p>
                </div>

                <div className="relative pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs z-10">
                  <span className="font-mono text-purple-400 font-medium flex items-center gap-1">
                    <span className="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse" />
                    {agent.stat}
                  </span>
                  <span className="text-slate-400 group-hover:text-purple-300 group-hover:translate-x-1 transition-all duration-200 flex items-center gap-1 font-semibold">
                    <span>Active</span>
                    <ArrowRight className="w-3.5 h-3.5" />
                  </span>
                </div>
              </div>
            );
          })}
        </div>

        {/* Feature 3: Bottom CTA Section */}
        <div className="mt-14 text-center">
          <a
            href="/ai-agents"
            className="inline-flex items-center gap-3 px-8 py-3.5 rounded-full font-semibold text-sm sm:text-base text-white bg-gradient-to-r from-purple-500 via-purple-600 to-cyan-600 shadow-[0_10px_30px_rgba(20,184,166,0.3)] hover:shadow-[0_15px_40px_rgba(20,184,166,0.5)] hover:scale-105 active:scale-95 transition-all duration-300 group cursor-pointer border border-purple-400/40"
          >
            <span>Explore Dedicated AI Suite</span>
            <ArrowRight className="w-4 h-4 text-white group-hover:translate-x-1.5 transition-transform duration-200" />
          </a>

          <p className="mt-3 text-xs text-slate-400 font-mono">
            Autonomous Multi-Agent Pipelines • Verified Revenue Attribution
          </p>
        </div>
      </div>

      <style>{`
        @keyframes falconRoboFloat {
          0%, 100% {
            transform: translateY(0px);
          }
          50% {
            transform: translateY(-8px);
          }
        }
        @keyframes falconVisorScan {
          0%, 100% {
            transform: translateX(-16px);
            opacity: 0.3;
          }
          50% {
            transform: translateX(16px);
            opacity: 1;
          }
        }
      `}</style>
    </section>
  );
};

export default FalconAgentsSection;

