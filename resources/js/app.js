import './bootstrap';


/**
 * Bootstrap 
 */
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap;

/**
 * Chart Js
 */
import Chart from 'chart.js/auto';
window.Chart = Chart;

/**
 * DataTables (Bootstrap 5 compatible)
 */
import DataTable from 'datatables.net-bs5';
window.DataTable = DataTable;

/**
 * Live (Base)
 */
import Live from './live/live';
window.Live = Live;

/**
 * Live (All) Game
 */
import LiveAll from './live/all';
window.LiveAll = LiveAll;

/**
 * Live (Possession) Game
 */
import LivePossession from './live/possession';
window.LivePossession = LivePossession;

/**
 * Formation Drawer
 */
import FormationDrawer from './formation-drawer';
window.FormationDrawer = FormationDrawer;

/**
 * Event Timeline
 */
import EventTimeline from './event-timeline';
window.EventTimeline = EventTimeline;

/**
 * Confirmation Modal 
 */
import ConfirmModal from './confirmation-modal';
window.ConfirmModal = ConfirmModal;
