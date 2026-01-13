<template>
  <div class="page">
    <div class="header">
      <h1>{{ $t('tasks.statsTitle') }}</h1>
      <NuxtLink to="/tasks" class="btn-secondary">{{ $t('tasks.exitReview') }}</NuxtLink>
    </div>

    <div class="controls card">
      <div class="date-range">
        <div class="control-group">
          <label>{{ $t('tasks.statStartDate') }}</label>
          <input type="month" v-model="startMonth" @change="fetchStats" class="month-input" />
        </div>
        <div class="control-group">
          <label>{{ $t('tasks.statEndDate') }}</label>
          <input type="month" v-model="endMonth" @change="fetchStats" class="month-input" />
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
    <div v-else class="stats-container">
      <!-- Chart Section -->
      <div class="chart-section card">
        <div class="chart-wrapper">
          <Bar v-if="chartData.labels.length > 0" :data="chartData" :options="chartOptions" />
          <div v-else class="no-data">No data available</div>
        </div>
      </div>

      <!-- Detailed Matrix Table Section -->
      <div class="details-section card">
        <h3>{{ $t('tasks.monthlyTotal') }}</h3>
        <div class="table-responsive">
          <table class="matrix-table">
            <thead>
              <tr>
                <th class="corner-cell"></th>
                <th v-for="month in stats" :key="month.month" class="header-month">
                  {{ month.month }}
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Total Row -->
              <tr class="total-row">
                <td class="row-header">Total</td>
                <td v-for="month in stats" :key="'total-'+month.month">
                  {{ month.total_points }}
                </td>
              </tr>
              <!-- Assignee Rows -->
              <tr v-for="assignee in uniqueAssignees" :key="assignee.id">
                <td class="row-header">{{ assignee.name }}</td>
                <td v-for="month in stats" :key="assignee.id+'-'+month.month">
                  {{ getAssigneePoints(month, assignee.id) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// ... imports ...
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

definePageMeta({
  middleware: ['auth']
})

const { token, user } = useAuth()
const config = useRuntimeConfig()
const apiBase = (config.public.apiBase as string) || ''
const router = useRouter()

// Redirect if not admin
if (user.value?.role !== 'admin') {
  router.push('/tasks')
}

const startMonth = ref('')
const endMonth = ref('')
const stats = ref<any[]>([])
const loading = ref(false)
const chartData = ref({
  labels: [] as string[],
  datasets: [] as any[]
})

// Compute unique assignees for the matrix table
const uniqueAssignees = computed(() => {
  const map = new Map<number, { id: number, name: string }>()
  stats.value.forEach(month => {
    month.assignee_stats.forEach((a: any) => {
      if (!map.has(a.id)) {
        map.set(a.id, { id: a.id, name: a.name })
      }
    })
  })
  return Array.from(map.values())
})

const getAssigneePoints = (monthStat: any, assigneeId: number) => {
  const found = monthStat.assignee_stats.find((a: any) => a.id === assigneeId)
  return found ? found.points : 0
}

// ... existing script code ...

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
    title: {
      display: true,
      text: 'Monthly Points by Assignee'
    },
    tooltip: {
      callbacks: {
        label: function(context: any) {
          return `${context.dataset.label}: ${context.raw}`
        }
      }
    }
  },
  scales: {
    x: {
      stacked: true,
    },
    y: {
      stacked: true,
      beginAtZero: true
    }
  }
}

// Colors for chart segments
const colors = [
  '#6366f1', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', 
  '#ef4444', '#3b82f6', '#14b8a6', '#f97316', '#64748b'
]
const getColor = (id: number) => colors[id % colors.length]

const processChartData = (data: any[]) => {
  if (!data || data.length === 0) {
    chartData.value = { labels: [], datasets: [] }
    return
  }

  const labels = data.map(d => d.month)
  
  // Find all unique assignees across all months
  const assigneeMap = new Map<number, string>()
  data.forEach(month => {
    month.assignee_stats.forEach((assignee: any) => {
      assigneeMap.set(assignee.id, assignee.name)
    })
  })

  // Create datasets
  const datasets: any[] = []
  let colorIndex = 0
  
  assigneeMap.forEach((name, id) => {
    const dataPoints = data.map(month => {
      const found = month.assignee_stats.find((a: any) => a.id === id)
      return found ? found.points : 0
    })

    datasets.push({
      label: name,
      data: dataPoints,
      backgroundColor: getColor(id),
      stack: 'Stack 0'
    })
    colorIndex++
  })

  chartData.value = {
    labels,
    datasets
  }
}

const fetchStats = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (startMonth.value) params.start_month = startMonth.value
    if (endMonth.value) params.end_month = endMonth.value

    const data = await $fetch<any[]>(`${apiBase}/api/tasks/statistics`, {
      headers: {
        Authorization: `Bearer ${token.value}`
      },
      params
    })
    stats.value = data
    processChartData(data)
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  } finally {
    loading.value = false
  }
}

// Initialize with last 12 months
onMounted(() => {
  const end = new Date()
  const start = new Date()
  start.setMonth(start.getMonth() - 11)
  
  endMonth.value = end.toISOString().slice(0, 7)
  startMonth.value = start.toISOString().slice(0, 7)
  
  fetchStats()
})
</script>

<style scoped>
.page {
  padding: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.controls {
  display: flex;
  gap: 2rem;
}

.date-range {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.month-input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.chart-wrapper {
  height: 400px;
  width: 100%;
  position: relative;
}

.no-data {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
}

.stats-table {
  width: 100%;
  border-collapse: collapse;
}

.stats-table th, .stats-table td {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  text-align: left;
}

.stats-table th {
  background: #f9fafb;
  font-weight: 600;
}

.month-cell {
  background: #f9fafb;
  font-weight: 600;
}

.btn-secondary {
  text-decoration: none;
  background: #f3f4f6;
  color: #374151;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

/* Matrix Table Styles */
.table-responsive {
  overflow-x: auto;
}

.matrix-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 800px; /* Ensure table doesn't squash on small screens */
}

.matrix-table th, .matrix-table td {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  text-align: center;
  white-space: nowrap;
}

.header-month {
  background-color: #22c55e; /* Green-500 */
  color: white;
  font-weight: 600;
}

.corner-cell {
  background-color: #f9fafb;
}

.total-row {
  background-color: #e0f2fe; /* Sky-100 */
  font-weight: 600;
  color: #0369a1;
}

.row-header {
  text-align: left;
  font-weight: 600;
  background-color: #f9fafb;
  position: sticky;
  left: 0;
  z-index: 10;
}

.matrix-table tbody tr:hover td:not(.row-header) {
  background-color: #f3f4f6;
}
</style>

