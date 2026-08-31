import { AppRoutes } from './routes/AppRoutes';
import { SiteSeo } from './components/seo/SiteSeo';
import { AnalyticsTracker } from './components/analytics/AnalyticsTracker';

export default function App() { return <><SiteSeo/><AnalyticsTracker/><AppRoutes/></>; }
