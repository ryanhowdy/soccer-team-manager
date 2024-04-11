import './bootstrap';


/**
 * Bootstrap 
 */
import bootstrap from 'bootstrap'

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
 * Live Game
 */
import Live from './live';
window.Live = Live;

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
