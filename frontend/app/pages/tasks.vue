<template>
  <div class="page">
    <div class="header">
      <h1>{{ $t('tasks.title') }}</h1>
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button 
          @click="isReviewMode = !isReviewMode" 
          :class="isReviewMode ? 'btn-secondary' : 'btn-primary'" 
        >
          {{ isReviewMode ? $t('tasks.exitReview') : $t('tasks.reviewTasks') }}
        </button>
        <button 
          @click="toggleCompletedMode" 
          :class="isCompletedMode ? 'btn-secondary' : 'btn-primary'" 
        >
          {{ isCompletedMode ? $t('tasks.exitCompleted') : $t('tasks.completedTasks') }}
        </button>
        
        <!-- Search box -->
        <input 
          v-model="searchQuery" 
          @input="handleSearch"
          type="text"
          :placeholder="$t('tasks.searchPlaceholder')"
          class="search-input"
        />
        
        <!-- Sort options for completed tasks -->
        <select v-if="isCompletedMode" v-model="sortOrder" @change="fetchCompletedTasks" class="sort-select">
          <option value="desc">{{ $t('tasks.sortDesc') }}</option>
          <option value="asc">{{ $t('tasks.sortAsc') }}</option>
        </select>
        
        <!-- Sort options for regular/review tasks -->
        <select v-if="!isCompletedMode" v-model="sortField" @change="handleSortChange" class="sort-select">
          <option value="id">{{ $t('tasks.sortById') }}</option>
          <option value="level">{{ $t('tasks.sortByLevel') }}</option>
          <option value="status">{{ $t('tasks.sortByStatus') }}</option>
          <option value="project">{{ $t('tasks.sortByProject') }}</option>
          <option value="department">{{ $t('tasks.sortByDepartment') }}</option>
          <option value="points">{{ $t('tasks.sortByPoints') }}</option>
          <option value="release_date">{{ $t('tasks.sortByReleaseDate') }}</option>
          <option value="start_date">{{ $t('tasks.sortByStartDate') }}</option>
          <option value="expected_finish_date">{{ $t('tasks.sortByExpectedFinish') }}</option>
          <option value="actual_finish_date">{{ $t('tasks.sortByActualFinish') }}</option>
        </select>
        
        <select v-if="!isCompletedMode" v-model="sortOrderRegular" @change="handleSortChange" class="sort-select-mini">
          <option value="desc">↓</option>
          <option value="asc">↑</option>
        </select>
        
        <button @click="showCreateModal = true" class="btn-primary" v-if="!isReviewMode && !isCompletedMode">{{ $t('tasks.addTask') }}</button>
        <button 
          v-if="isAdmin" 
          @click="navigateTo('/stats')" 
          class="btn-secondary"
        >
          {{ $t('tasks.statistics') }}
        </button>
      </div>
    </div>

    <div v-if="pending" class="loading">{{ $t('login.loggingIn') ? $t('login.loggingIn') : 'Loading...' }}</div>
    <div v-else-if="error" class="error">{{ error.message }}</div>
    
    <div v-else class="content-wrapper">
      <!-- Regular Tasks View -->
      <div v-if="!isCompletedMode">
      <!-- Desktop View (Table) -->
      <div class="desktop-view">
        <table class="task-table">
          <thead>
            <tr>
              <th>{{ $t('tasks.idx') }}</th>
              <th>{{ $t('tasks.level') }}</th>
              <th>{{ $t('tasks.status') }}</th>
              <th>{{ $t('tasks.assignee') }}</th>
              <th>{{ $t('tasks.relatedPersonnel') }}</th>
              <th>{{ $t('tasks.project') }}</th>
              <th>{{ $t('tasks.item') }}</th>
              <th>{{ $t('tasks.department') }}</th>
              <th>{{ $t('tasks.work') }}</th>
              <th>{{ $t('tasks.points') }}</th>
              <th>{{ $t('tasks.releaseDate') }}</th>
              <th>{{ $t('tasks.startDate') }}</th>
              <th>{{ $t('tasks.expectedFinishDate') }}</th>
              <th>{{ $t('tasks.actualFinishDate') }}</th>
              <th>{{ $t('tasks.outputUrl') }}</th>
              <th>{{ $t('tasks.memo') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in tasks" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.level }}</td>
              <td>
                <span :class="['status-badge', item.status.replace(' ', '-')]">
                  {{ $t(`tasks.${item.status.replace(' ', '_')}`) }}
                </span>
              </td>
              <td class="user-cell clickable" @click="startEditingAssignee(item)">
                <div v-if="editingAssigneeId === item.id" class="edit-select-wrapper" @click.stop>
                  <select 
                    :value="item.user_id" 
                    @change="updateAssignee(item, ($event.target as HTMLSelectElement).value)"
                    @blur="editingAssigneeId = null"
                    ref="assigneeSelect"
                  >
                    <option v-for="opt in userOptions" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </div>
                <div v-else class="user-info">
                  <img v-if="item.assignee" :src="item.assignee.photo_url || 'https://ui-avatars.com/api/?name=' + item.assignee.name" class="avatar" />
                  {{ item.assignee?.name || '-' }}
                </div>
              </td>
              <td>{{ item.related_personnel || '-' }}</td>
              <td>{{ item.project }}</td>
              <td class="clickable" @click="openDetails(item)">{{ item.item || '-' }}</td>
              <td>{{ item.department }}</td>
              <td class="work-cell clickable" @click="openDetails(item)">{{ item.work }}</td>
              <td>{{ item.points }}</td>
              <td>{{ item.release_date || '-' }}</td>
              <td>{{ item.start_date || '-' }}</td>
              <td>{{ item.expected_finish_date || '-' }}</td>
              <td>{{ item.actual_finish_date || '-' }}</td>
              <td>
                <a v-if="item.output_url" :href="item.output_url" target="_blank" class="link-btn">🔗</a>
                <span v-else>-</span>
              </td>
              <td class="memo-cell">
                <div class="memo-list">
                  <div v-if="item.remarks && item.remarks.length > 0" class="memo-item">
                    <span class="memo-user">{{ item.remarks[item.remarks.length - 1]?.user_name }}:</span>
                    <span class="memo-content">{{ item.remarks[item.remarks.length - 1]?.content }}</span>
                  </div>
                </div>
                <div class="memo-add">
                  <input 
                    v-model="newRemarks[item.id]" 
                    :placeholder="$t('schedules.addMemoPlaceHolder')" 
                    @keyup.enter="handlePostRemark(item.id)"
                  />
                  <button @click="handlePostRemark(item.id)" :disabled="postingRemark === item.id">
                    {{ postingRemark === item.id ? '...' : '➔' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile View (Cards) -->
      <div class="mobile-view">
        <div v-for="item in tasks" :key="item.id" class="card">
          <div class="card-header">
            <span class="idx">#{{ item.id }}</span>
            <span :class="['status', item.status.replace(' ', '-')]">{{ $t(`tasks.${item.status.replace(' ', '_')}`) }}</span>
          </div>
          <div class="card-body">
            <h2 class="work-title clickable" @click="openDetails(item)">{{ item.work }}</h2>
            <div class="info-grid">
              <div class="info-item"><strong>{{ $t('tasks.project') }}:</strong> {{ item.project }}</div>
              <div class="info-item"><strong>{{ $t('tasks.assignee') }}:</strong> {{ item.assignee?.name || '-' }}</div>
              <div class="info-item"><strong>{{ $t('tasks.expectedFinishDate') }}:</strong> {{ item.expected_finish_date || '-' }}</div>
            </div>
            <div class="memo-board">
              <strong>{{ $t('tasks.memo') }}:</strong>
              <div class="memo-list mobile">
                <div v-if="item.remarks && item.remarks.length > 0" class="memo-item">
                  <span class="memo-user">{{ item.remarks[item.remarks.length - 1]?.user_name }}:</span>
                  <span class="memo-content">{{ item.remarks[item.remarks.length - 1]?.content }}</span>
                </div>
              </div>
              <div class="memo-add mobile">
                <input 
                  v-model="newRemarks[item.id]" 
                  :placeholder="$t('schedules.addMemoPlaceHolder')"
                  @keyup.enter="handlePostRemark(item.id)"
                />
                <button @click="handlePostRemark(item.id)" :disabled="postingRemark === item.id">
                  {{ postingRemark === item.id ? '...' : '➔' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
      
      <!-- Completed Tasks View -->
      <div v-if="isCompletedMode" class="completed-tasks-view">
        <div class="desktop-view">
          <table class="task-table">
            <thead>
              <tr>
                <th>{{ $t('tasks.idx') }}</th>
                <th>{{ $t('tasks.level') }}</th>
                <th>{{ $t('tasks.status') }}</th>
                <th>{{ $t('tasks.assignee') }}</th>
                <th>{{ $t('tasks.relatedPersonnel') }}</th>
                <th>{{ $t('tasks.project') }}</th>
                <th>{{ $t('tasks.item') }}</th>
                <th>{{ $t('tasks.department') }}</th>
                <th>{{ $t('tasks.work') }}</th>
                <th>{{ $t('tasks.points') }}</th>
                <th>{{ $t('tasks.releaseDate') }}</th>
                <th>{{ $t('tasks.startDate') }}</th>
                <th>{{ $t('tasks.expectedFinishDate') }}</th>
                <th>{{ $t('tasks.actualFinishDate') }}</th>
                <th>{{ $t('tasks.reviewStatus') }}</th>
                <th>{{ $t('tasks.reviewer') }}</th>
                <th>{{ $t('tasks.reviewedAt') }}</th>
                <th>{{ $t('tasks.memo') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in completedTasks" :key="item.id" @click="openDetails(item)" style="cursor: pointer;">
                <td>{{ item.id }}</td>
                <td>{{ item.level }}</td>
                <td>
                  <span :class="['status-badge', item.status.replace(' ', '-')]">
                    {{ $t(`tasks.${item.status.replace(' ', '_')}`) }}
                  </span>
                </td>
                <td>
                  <div class="user-info">
                    <img v-if="item.assignee" :src="item.assignee.photo_url || 'https://ui-avatars.com/api/?name=' + item.assignee.name" class="avatar" />
                    {{ item.assignee?.name || '-' }}
                  </div>
                </td>
                <td>{{ item.related_personnel || '-' }}</td>
                <td>{{ item.project }}</td>
                <td>{{ item.item || '-' }}</td>
                <td>{{ item.department }}</td>
                <td class="work-cell">{{ item.work }}</td>
                <td>{{ item.points }}</td>
                <td>{{ item.release_date || '-' }}</td>
                <td>{{ item.start_date || '-' }}</td>
                <td>{{ item.expected_finish_date || '-' }}</td>
                <td>{{ item.actual_finish_date || '-' }}</td>
                <td>
                  <span v-if="item.review_status && item.review_status !== 'unsubmitted'" :class="['status-badge', item.review_status]">
                    {{ $t(`tasks.reviewStatus_${item.review_status}`) }}
                  </span>
                  <span v-else>-</span>
                </td>
                <td>{{ item.reviewer?.name || '-' }}</td>
                <td>{{ item.reviewed_at ? formatDateTime(item.reviewed_at) : '-' }}</td>
                <td class="memo-cell">
                  <div class="memo-list">
                    <div v-if="item.remarks && item.remarks.length > 0" class="memo-item">
                      <span class="memo-user">{{ item.remarks[item.remarks.length - 1]?.user_name }}:</span>
                      <span class="memo-content">{{ item.remarks[item.remarks.length - 1]?.content }}</span>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile View for Completed Tasks -->
        <div class="mobile-view">
          <div v-for="item in completedTasks" :key="item.id" class="card" @click="openDetails(item)">
            <div class="card-header">
              <span class="idx">#{{ item.id }}</span>
              <span :class="['status', item.status.replace(' ', '-')]">{{ $t(`tasks.${item.status.replace(' ', '_')}`) }}</span>
            </div>
            <div class="card-body">
              <h2 class="work-title">{{ item.work }}</h2>
              <div class="info-grid">
                <div class="info-item"><strong>{{ $t('tasks.project') }}:</strong> {{ item.project }}</div>
                <div class="info-item"><strong>{{ $t('tasks.assignee') }}:</strong> {{ item.assignee?.name || '-' }}</div>
                <div class="info-item"><strong>{{ $t('tasks.reviewer') }}:</strong> {{ item.reviewer?.name || '-' }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="completedPagination" class="pagination">
          <button 
            @click="goToPage(completedPagination.current_page - 1)" 
            :disabled="completedPagination.current_page === 1"
            class="btn-secondary"
          >
            ← {{ $t('common.previous') || '上一頁' }}
          </button>
          <span class="page-info">
            {{ completedPagination.from }} - {{ completedPagination.to }} / {{ completedPagination.total }}
          </span>
          <button 
            @click="goToPage(completedPagination.current_page + 1)" 
            :disabled="completedPagination.current_page === completedPagination.last_page"
            class="btn-secondary"
          >
            {{ $t('common.next') || '下一頁' }} →
          </button>
        </div>

        <div v-if="completedTasks.length === 0" class="empty">
          {{ $t('tasks.noCompletedTasks') || '沒有已完成的任務' }}
        </div>
      </div>
      
      <div v-if="!isCompletedMode && tasks?.length === 0" class="empty">
        No tasks found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" :title="$t('tasks.addTask')" class="task-modal">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.user_id" 
              :label="$t('tasks.assignee')" 
              type="select" 
              :options="userOptions" 
              required
              :error="errors.user_id"
            />
            <BaseInput 
              v-model="form.related_personnel" 
              :label="$t('tasks.relatedPersonnel')" 
              type="select" 
              :options="userNameOptions" 
              :error="errors.related_personnel"
            />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.project" 
              :label="$t('tasks.project')" 
              type="select" 
              :options="projectOptions"
              required 
              :error="errors.project"
            />
            <BaseInput v-model="form.item" :label="$t('tasks.item')" placeholder="E.g. 技術" :error="errors.item" />
            <BaseInput 
              v-model="form.department" 
              :label="$t('tasks.department')" 
              type="select" 
              :options="departmentOptions"
              required 
              :error="errors.department"
            />
            <BaseInput v-model="form.points" :label="$t('tasks.points')" type="number" step="0.5" required :error="errors.points" />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput v-model="form.release_date" :label="$t('tasks.releaseDate')" type="date" />
            <BaseInput v-model="form.start_date" :label="$t('tasks.startDate')" type="date" />
            <BaseInput v-model="form.expected_finish_date" :label="$t('tasks.expectedFinishDate')" type="date" />
            <BaseInput v-model="form.actual_finish_date" :label="$t('tasks.actualFinishDate')" type="date" />
          </div>
        </div>

        <div class="form-section no-border">
          <div class="form-grid">
            <div class="full-width">
              <BaseInput v-model="form.work" :label="$t('tasks.work')" placeholder="Task description..." required :error="errors.work" />
            </div>
            <BaseInput v-model="form.output_url" :label="$t('tasks.outputUrl')" placeholder="https://..." :error="errors.output_url" />
            <div class="full-width">
              <BaseInput v-model="form.memo" :label="$t('tasks.memo')" type="textarea" placeholder="Remarks..." :error="errors.memo" />
            </div>
          </div>
        </div>

        <div v-if="createError" class="error-box">
          {{ createError }}
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="btn-secondary">{{ $t('tasks.cancel') }}</button>
        <button @click="handleCreate" :disabled="creating" class="btn-primary">
          {{ creating ? $t('tasks.creating') : $t('tasks.create') }}
        </button>
      </template>
    </BaseModal>
    
    <!-- Details/Edit Modal -->
    <BaseModal v-model="showDetailsModal" :title="isEditingTasks ? $t('tasks.editTask') : $t('tasks.details') || $t('schedules.details')" class="task-modal">
      <div v-if="!isEditingTasks" class="details-view">
        <div class="form-grid">
          <div class="detail-item">
              <label>{{ $t('tasks.id') || 'ID' }}</label>
              <div class="value">#{{ selectedTask?.id }}</div>
          </div>
          <div class="detail-item">
              <label>{{ $t('tasks.level') }}</label>
              <div class="value">{{ selectedTask?.level }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.project') }}</label>
            <div class="value">{{ selectedTask?.project }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.item') }}</label>
            <div class="value">{{ selectedTask?.item || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.status') }}</label>
            <div class="value">
              <span :class="['status-badge', selectedTask?.status.replace(' ', '-')]">
                {{ selectedTask ? $t(`tasks.${selectedTask.status.replace(' ', '_')}`) : '' }}
              </span>
            </div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.assignee') }}</label>
            <div class="value">{{ selectedTask?.assignee?.name || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.department') }}</label>
            <div class="value">{{ selectedTask?.department }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.points') }}</label>
            <div class="value">{{ selectedTask?.points }}</div>
          </div>
        </div>

        <div class="detail-item full-width">
            <label>{{ $t('tasks.work') }}</label>
            <div class="value work-content">{{ selectedTask?.work }}</div>
        </div>

        <div class="form-grid">
          <div class="detail-item">
            <label>{{ $t('tasks.releaseDate') }}</label>
            <div class="value">{{ selectedTask?.release_date || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.startDate') }}</label>
            <div class="value">{{ selectedTask?.start_date || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.expectedFinishDate') }}</label>
            <div class="value">{{ selectedTask?.expected_finish_date || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.actualFinishDate') }}</label>
            <div class="value">{{ selectedTask?.actual_finish_date || '-' }}</div>
          </div>
        </div>
        
        <!-- Review Information -->
        <div v-if="selectedTask?.review_status && selectedTask.review_status !== 'unsubmitted'" class="review-info-section">
          <h3 style="margin-bottom: 1rem; color: #495057;">{{ $t('tasks.reviewInfo') || '審核資訊' }}</h3>
          <div class="form-grid">
            <div class="detail-item">
              <label>{{ $t('tasks.reviewStatus') || '審核狀態' }}</label>
              <div class="value">
                <span :class="['status-badge', selectedTask.review_status]">
                  {{ $t(`tasks.reviewStatus_${selectedTask.review_status}`) || selectedTask.review_status }}
                </span>
              </div>
            </div>
            <div v-if="selectedTask.reviewer" class="detail-item">
              <label>{{ $t('tasks.reviewer') || '審核者' }}</label>
              <div class="value">{{ selectedTask.reviewer.name }}</div>
            </div>
            <div v-if="selectedTask.reviewed_at" class="detail-item">
              <label>{{ $t('tasks.reviewedAt') || '審核時間' }}</label>
              <div class="value">{{ formatDateTime(selectedTask.reviewed_at) }}</div>
            </div>
            <div v-if="selectedTask.approved_at" class="detail-item">
              <label>{{ $t('tasks.approvedAt') || '通過時間' }}</label>
              <div class="value">{{ formatDateTime(selectedTask.approved_at) }}</div>
            </div>
            <div v-if="selectedTask.failed_at" class="detail-item">
              <label>{{ $t('tasks.failedAt') || '失敗時間' }}</label>
              <div class="value">{{ formatDateTime(selectedTask.failed_at) }}</div>
            </div>
          </div>
        </div>
        
        <div class="detail-item">
          <label>{{ $t('tasks.memo') }} ({{ $t('common.management') }})</label>
          <div class="value memo-content-box">{{ selectedTask?.memo || '-' }}</div>
        </div>

        <div class="detail-memos">
          <label>{{ $t('tasks.memo') }} ({{ $t('tasks.remarks') || 'Remarks' }})</label>
          <div class="memo-list modal-memos">
            <div v-for="remark in selectedTask?.remarks" :key="remark.id" class="memo-item">
              <span class="memo-user">{{ remark.user_name }}:</span>
              <span class="memo-content">{{ remark.content }}</span>
              <span class="memo-time">{{ formatTime(remark.created_at) }}</span>
            </div>
          </div>
          <div class="memo-add">
            <input 
              v-model="newRemarks[selectedTask!.id]" 
              :placeholder="$t('schedules.addMemoPlaceHolder')" 
              @keyup.enter="handlePostRemark(selectedTask!.id)"
            />
            <button @click="handlePostRemark(selectedTask!.id)" :disabled="postingRemark === selectedTask!.id">
              {{ postingRemark === selectedTask!.id ? '...' : '➔' }}
            </button>
          </div>
        </div>
      </div>

      <form v-else @submit.prevent="handleUpdate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="taskEditForm.user_id" 
              :label="$t('tasks.assignee')" 
              type="select" 
              :options="userOptions" 
            />
            <BaseInput 
              v-model="taskEditForm.status" 
              :label="$t('tasks.status')" 
              type="select" 
              :options="[
                { label: $t('tasks.in_progress'), value: 'in progress' },
                { label: $t('tasks.working'), value: 'working' },
                { label: $t('tasks.idle'), value: 'idle' },
                { label: $t('tasks.finished'), value: 'finished' }
              ]" 
            />
             <BaseInput 
              v-model="taskEditForm.level" 
              :label="$t('tasks.level')" 
              type="select" 
              :options="[
                { label: $t('tasks.ordinary'), value: 1 },
                { label: $t('tasks.important'), value: 2 },
                { label: $t('tasks.priority'), value: 3 }
              ]" 
            />
             <BaseInput v-model="taskEditForm.points" :label="$t('tasks.points')" type="number" step="0.5" />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput v-model="taskEditForm.release_date" :label="$t('tasks.releaseDate')" type="date" />
            <BaseInput v-model="taskEditForm.start_date" :label="$t('tasks.startDate')" type="date" />
            <BaseInput v-model="taskEditForm.expected_finish_date" :label="$t('tasks.expectedFinishDate')" type="date" />
            <BaseInput v-model="taskEditForm.actual_finish_date" :label="$t('tasks.actualFinishDate')" type="date" />
          </div>
        </div>

        <div class="form-section no-border">
          <div class="form-grid">
            <div class="full-width">
              <BaseInput v-model="taskEditForm.work" :label="$t('tasks.work')" />
            </div>
            <div class="full-width">
              <BaseInput v-model="taskEditForm.memo" :label="$t('tasks.memo')" type="textarea" />
            </div>
          </div>
        </div>
      </form>

      <template #footer>
        <template v-if="isReviewMode">
          <button @click="showDetailsModal = false" class="btn-secondary">{{ $t('common.cancel') || 'Cancel' }}</button>
          <button v-if="isAuditor" @click="handleReview('approved')" class="btn-success" style="background: #10b981; color: white;">
            {{ $t('tasks.reviewPass') || 'Pass' }}
          </button>
          <button v-if="isAuditor" @click="handleReview('failed')" class="btn-danger" style="background: #ef4444; color: white;">
            {{ $t('tasks.reviewFail') || 'Fail' }}
          </button>
        </template>
        <template v-else>
          <button v-if="!isEditingTasks" @click="showDetailsModal = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
          <button v-if="!isEditingTasks" @click="startEditingTask" class="btn-primary">{{ $t('schedules.edit') }}</button>
          
          <button v-if="isEditingTasks" @click="isEditingTasks = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
          <button v-if="isEditingTasks" @click="handleUpdate" :disabled="updatingTasks" class="btn-primary">
            {{ updatingTasks ? $t('schedules.updating') : $t('schedules.save') }}
          </button>
        </template>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { token, user } = useAuth() // Assume user is available from useAuth
const { t } = useI18n()
const config = useRuntimeConfig()
const apiBase = (config.public.apiBase as string) || ''
const showCreateModal = ref(false)
const showDetailsModal = ref(false)
const isEditingTasks = ref(false)
const isReviewMode = ref(false) // Toggle for review list
const isCompletedMode = ref(false) // Toggle for completed list
const sortOrder = ref('desc') // Sort order for completed tasks
const searchQuery = ref('') // Search query
const sortField = ref('id') // Sort field for regular tasks
const sortOrderRegular = ref('desc') // Sort order for regular tasks
const creating = ref(false)
const updatingTasks = ref(false)
const createError = ref('')
const errors = reactive<Record<string, string>>({})
const postingRemark = ref<number | null>(null)
const newRemarks = ref<Record<number, string>>({})
const editingAssigneeId = ref<number | null>(null)
const assigneeSelect = ref<HTMLSelectElement | null>(null)
const currentTaskId = ref<number | null>(null)
const completedTasks = ref<Task[]>([])
const completedPagination = ref<any>(null)

const form = reactive({
  level: 1,
  status: 'in progress',
  user_id: '',
  related_personnel: '',
  project: '',
  item: '',
  department: '',
  work: '',
  points: 1.0,
  release_date: '',
  start_date: '',
  expected_finish_date: '',
  actual_finish_date: '',
  output_url: '',
  memo: ''
})

const taskEditForm = reactive({
  id: 0,
  level: 1,
  status: '',
  review_status: 'unsubmitted',
  user_id: 0,
  related_personnel: '',
  project: '',
  item: '',
  department: '',
  work: '',
  points: 0,
  release_date: '',
  start_date: '',
  expected_finish_date: '',
  actual_finish_date: '',
  output_url: '',
  memo: ''
})

interface Task {
  id: number;
  level: number;
  status: string;
  review_status: string;
  user_id: number;
  related_personnel?: string;
  project: string;
  item?: string;
  department: string;
  work: string;
  points: number;
  release_date?: string;
  start_date?: string;
  expected_finish_date?: string;
  actual_finish_date?: string;
  output_url?: string;
  memo?: string;
  remarks: TaskRemark[];
  assignee?: {
    id: number;
    name: string;
    photo_url?: string;
  };
  reviewer?: {
    id: number;
    name: string;
    photo_url?: string;
  };
  reviewed_by?: number;
  reviewed_at?: string;
  approved_at?: string;
  failed_at?: string;
}

interface TaskRemark {
  id: number;
  user_name: string;
  content: string;
  created_at: string;
}

const fetchParams = computed(() => {
  const params: any = {}
  
  if (isReviewMode.value) {
    params.review_status = 'submitted'
  } else {
    params.exclude_review_status = 'approved,failed'
  }
  
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  
  params.sort_field = sortField.value
  params.sort_order = sortOrderRegular.value
  
  return params
})

const { data: tasks, pending, error, refresh } = await useFetch<Task[]>(`${apiBase}/api/tasks`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  },
  query: fetchParams
})

const selectedTask = computed(() => {
  if (!currentTaskId.value || !tasks.value) return null
  return tasks.value.find(t => t.id === currentTaskId.value) || null
})

const { data: users } = await useFetch<any[]>(`${apiBase}/api/users`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: projectsData } = await useFetch<any[]>(`${apiBase}/api/projects`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: departmentsData } = await useFetch<any[]>(`${apiBase}/api/departments`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const projectOptions = computed(() => {
  if (!projectsData.value) return []
  return projectsData.value.map(p => ({ label: p.name, value: p.name }))
})

const departmentOptions = computed(() => {
  if (!departmentsData.value) return []
  return departmentsData.value.map(d => ({ label: d.name, value: d.name }))
})

const userOptions = computed(() => {
  if (!users.value) return []
  return users.value.map((u: any) => ({ label: u.name, value: u.id }))
})

const userNameOptions = computed(() => {
  if (!users.value) return [{ label: '-', value: '' }]
  return [
    { label: '-', value: '' },
    ...users.value.map((u: any) => ({ label: u.name, value: u.name }))
  ]
})

const isAuditor = computed(() => ['auditor', 'admin', 'manager'].includes(user.value?.role))
const isAdmin = computed(() => user.value?.role === 'admin')

const formatTime = (dateStr: string) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString([], { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const formatDateTime = (dateStr: string) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-TW', { 
    year: 'numeric',
    month: '2-digit', 
    day: '2-digit', 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false
  })
}

const openDetails = (task: Task) => {
  currentTaskId.value = task.id
  isEditingTasks.value = false
  showDetailsModal.value = true
}

const startEditingTask = () => {
  // If in review mode, we generally don't edit, but "Review" action is separate.
  // Unless we want to effectively use "Edit" mode as "Review" mode but readonly?
  // Let's keep Edit for normal users. Reviewers use the Review buttons.
  if (!selectedTask.value) return
  Object.assign(taskEditForm, {
    id: selectedTask.value.id,
    level: selectedTask.value.level,
    status: selectedTask.value.status,
    review_status: selectedTask.value.review_status,
    user_id: selectedTask.value.user_id,
    related_personnel: selectedTask.value.related_personnel || '',
    project: selectedTask.value.project,
    item: selectedTask.value.item || '',
    department: selectedTask.value.department,
    work: selectedTask.value.work,
    points: selectedTask.value.points,
    release_date: selectedTask.value.release_date || '',
    start_date: selectedTask.value.start_date || '',
    expected_finish_date: selectedTask.value.expected_finish_date || '',
    actual_finish_date: selectedTask.value.actual_finish_date || '',
    output_url: selectedTask.value.output_url || '',
    memo: selectedTask.value.memo || ''
  })
  isEditingTasks.value = true
}

const handleUpdate = async () => {
  if (!taskEditForm.id) return
  updatingTasks.value = true
  try {
    await $fetch(`${apiBase}/api/tasks/${taskEditForm.id}`, {
      method: 'PUT',
      body: taskEditForm,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    await refresh()
    isEditingTasks.value = false
  } catch (err) {
    console.error('Failed to update task:', err)
  } finally {
    updatingTasks.value = false
  }
}

const handleReview = async (action: 'approved' | 'failed') => {
  if (!selectedTask.value) return
  updatingTasks.value = true
  try {
    const payload:any = { review_status: action }
    // If failed, also set status to failed (or matching logic)
    // The backend logic says: if review_status=failed, status=failed.
    // So sending review_status=failed is enough.
    
    await $fetch(`${apiBase}/api/tasks/${selectedTask.value.id}`, {
      method: 'PUT',
      body: payload,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    await refresh()
    showDetailsModal.value = false
  } catch (err) {
    console.error('Failed to review task:', err)
  } finally {
    updatingTasks.value = false
  }
}

const handlePostRemark = async (taskId: number) => {
  const content = newRemarks.value[taskId]
  if (!content || !content.trim()) return

  postingRemark.value = taskId
  try {
    await $fetch(`${apiBase}/api/tasks/${taskId}/remarks`, {
      method: 'POST',
      body: { content },
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    newRemarks.value[taskId] = ''
    refresh()
  } catch (err) {
    console.error('Failed to post remark:', err)
  } finally {
    postingRemark.value = null
  }
}

const startEditingAssignee = (task: Task) => {
  if (isReviewMode.value) return // Disable quick edit in review mode
  editingAssigneeId.value = task.id
  nextTick(() => {
    assigneeSelect.value?.focus()
  })
}

const updateAssignee = async (task: Task, newUserId: string) => {
  if (Number(newUserId) === task.user_id) {
    editingAssigneeId.value = null
    return
  }

  try {
    const selectedUser = users.value?.find((u: any) => u.id === Number(newUserId))
    const payload: any = { user_id: Number(newUserId) }
    
    // Also update department if user changed
    if (selectedUser?.department?.name) {
      payload.department = selectedUser.department.name
    }

    await $fetch(`${apiBase}/api/tasks/${task.id}`, {
      method: 'PUT',
      body: payload,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    refresh()
  } catch (err) {
    console.error('Failed to update assignee:', err)
    alert('Failed to update assignee. You might not have permission.')
  } finally {
    editingAssigneeId.value = null
  }
}

const toggleCompletedMode = () => {
  isCompletedMode.value = !isCompletedMode.value
  if (isCompletedMode.value) {
    isReviewMode.value = false // Exit review mode when entering completed mode
    fetchCompletedTasks()
  }
}

const fetchCompletedTasks = async () => {
  try {
    const params: any = {
      sort: sortOrder.value,
      per_page: 20
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    const response = await $fetch<any>(`${apiBase}/api/tasks/completed`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      },
      params
    })
    completedTasks.value = response.data
    completedPagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to
    }
  } catch (err) {
    console.error('Failed to fetch completed tasks:', err)
  }
}

const goToPage = async (page: number) => {
  try {
    const response = await $fetch<any>(`${apiBase}/api/tasks/completed`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      },
      params: {
        sort: sortOrder.value,
        per_page: 20,
        page
      }
    })
    completedTasks.value = response.data
    completedPagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to
    }
  } catch (err) {
    console.error('Failed to fetch page:', err)
  }
}

const handleSearch = () => {
  refresh()
}

const handleSortChange = () => {
  refresh()
}

watch(projectOptions, (newOptions) => {
  if (newOptions && newOptions.length > 0 && !form.project) {
    form.project = newOptions[0]?.value as string || ''
  }
}, { immediate: true })

watch(userOptions, (options) => {
  if (options && options.length > 0 && !form.user_id) {
    const firstOption = options[0]
    if (firstOption) {
      form.user_id = firstOption.value
    }
  }
}, { immediate: true })

// Auto-fill department when user changes
watch(() => form.user_id, (newUserId) => {
  if (!newUserId || !users.value) return
  const selectedUser = users.value.find((u: any) => u.id === Number(newUserId))
  if (selectedUser?.department?.name) {
    form.department = selectedUser.department.name
  }
})

const handleCreate = async () => {
  creating.value = true
  createError.value = ''
  // Reset per-field errors
  Object.keys(errors).forEach(key => delete errors[key])

  // Validate required fields
  if (!form.user_id) errors.user_id = t('validation.required', { field: t('tasks.assignee') })
  if (!form.project) errors.project = t('validation.required', { field: t('tasks.project') })
  if (!form.department) errors.department = t('validation.required', { field: t('tasks.department') })
  if (!form.work) errors.work = t('validation.required', { field: t('tasks.work') })
  if (form.points === undefined || form.points === null) errors.points = t('validation.required', { field: t('tasks.points') })

  // Validate: assignee and related_personnel cannot be the same
  if (form.related_personnel && form.user_id) {
    const assignee = users.value?.find((u: any) => u.id === Number(form.user_id))
    if (assignee && form.related_personnel === assignee.name) {
      errors.related_personnel = t('tasks.cannotBeSameAsAssignee')
    }
  }

  if (Object.keys(errors).length > 0) {
    createError.value = t('validation.errorOccurred')
    creating.value = false
    return
  }

  try {
    await $fetch(`${apiBase}/api/tasks`, {
      method: 'POST',
      body: form,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    showCreateModal.value = false
    refresh()
    // Reset form with defaults from options if available
    Object.assign(form, {
      level: 1,
      status: 'in progress',
      user_id: userOptions.value[0]?.value || '',
      related_personnel: '',
      project: projectOptions.value[0]?.value || '',
      item: '',
      department: departmentOptions.value[0]?.value || '',
      work: '',
      points: 1.0,
      release_date: '',
      start_date: '',
      expected_finish_date: '',
      actual_finish_date: '',
      output_url: '',
      memo: ''
    })
  } catch (err: any) {
    if (err.status === 422 && err.data?.errors) {
      // Map Laravel validation errors to our local errors object
      Object.keys(err.data.errors).forEach(key => {
        errors[key] = err.data.errors[key][0]
      })
      createError.value = t('validation.errorOccurred')
    } else {
      createError.value = err.data?.message || 'Failed to create task.'
    }
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
.page {
  padding: 1rem;
  margin: 0 auto;
}

/* PC / Large Screen Styles (> 1024px) */
@media (min-width: 1025px) {
  .mobile-view { display: none; }
  .page { max-width: 1920px; width: 95%; }
}

/* Tablet/Mobile Styles */
@media (max-width: 1024px) {
  .desktop-view { display: none; }
  .page { max-width: 700px; }
}

@media (max-width: 480px) {
  .page { max-width: 100%; }
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-primary {
  background: #764ba2;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.desktop-view {
  overflow-x: auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.task-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
  min-width: 1600px;
}

.task-table th, .task-table td {
  border: 1px solid #e5e7eb;
  padding: 8px;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
}

.task-table th {
  background: #ffff00; /* Yellow header matching screenshot */
  color: #000;
  font-weight: 600;
}

.task-table tbody tr {
  transition: background-color 0.2s ease;
}

.task-table tbody tr:hover {
  background-color: #f8f6ff; /* Consistent light purple hover */
}

.work-cell {
  text-align: left !important;
  min-width: 300px;
  white-space: normal !important;
  color: #1a0dab;
}

.memo-cell {
  min-width: 300px;
  max-width: 400px;
  text-align: left !important;
  vertical-align: top;
}

.memo-board {
  margin-top: 0.8rem;
  padding: 0.8rem;
  background: #f9fafb;
  border-radius: 8px;
  font-size: 0.85rem;
}

.memo-list {
  max-height: 150px;
  overflow-y: auto;
  margin-bottom: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.memo-item {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  line-height: 1.4;
  text-align: left;
}

.memo-user {
  font-weight: 600;
  color: #4b5563;
  margin-right: 6px;
}

.memo-content {
  color: #1f2937;
}

.memo-add {
  display: flex;
  gap: 4px;
}

.memo-add input {
  flex: 1;
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.8rem;
}

.memo-add button {
  background: #764ba2;
  color: white;
  border: none;
  padding: 4px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

.memo-add button:disabled {
  opacity: 0.5;
}

.clickable {
  cursor: pointer;
  color: #764ba2;
  transition: all 0.2s;
}

.clickable:hover {
  text-decoration: underline;
  opacity: 0.8;
}

.details-view {
  padding: 1rem 0;
}

.detail-item {
  margin-bottom: 1.25rem;
}

.detail-item label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.4rem;
  display: block;
}

.detail-item .value {
  font-size: 0.95rem;
  color: #1f2937;
  font-weight: 500;
}

.work-content {
  white-space: pre-wrap;
  line-height: 1.6;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #f3f4f6;
}

.memo-content-box {
  white-space: pre-wrap;
  line-height: 1.6;
}

.detail-memos {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f3f4f6;
}

.detail-memos label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
  display: block;
}

.modal-memos {
  max-height: 300px;
  overflow-y: auto;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.memo-time {
  font-size: 0.7rem;
  color: #9ca3af;
  margin-left: auto;
}

.user-cell.clickable {
  cursor: pointer;
  transition: background-color 0.2s;
}

.user-cell.clickable:hover {
  background-color: #f3f4f6;
}

.edit-select-wrapper {
  padding: 4px;
}

.edit-select-wrapper select {
  width: 100%;
  padding: 4px;
  border-radius: 4px;
  border: 1px solid #d1d5db;
  font-size: 0.8rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
}

.avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.75rem;
  display: inline-block;
  min-width: 80px;
}

/* Matching UI colors from spreadsheet */
.status-badge.working { background: #00ff00; color: #000; }
.status-badge.in-progress { background: #d3d3d3; color: #000; }
.status-badge.idle { background: #4a90e2; color: #fff; }
.status-badge.finished { background: #f5a623; color: #fff; }

.link-btn { text-decoration: none; font-size: 1rem; }

/* Mobile Card Styles */
.card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  border-left: 4px solid #764ba2;
}

.card-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.idx { font-weight: bold; color: #999; }

.status {
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 4px;
}

.status.working { background: #e8f5e9; color: #2e7d32; }

.work-title {
  font-size: 1rem;
  margin: 0.5rem 0;
  color: #1f2937;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: #6b7280;
}

/* Modal styles */
.task-modal :deep(.modal-content) {
  max-width: 800px;
  width: 90%;
}

.modal-form {
  display: flex;
  flex-direction: column;
  width: 100%;
  box-sizing: border-box;
}

.form-section {
  width: 100%;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f3f4f6;
  box-sizing: border-box;
}

.form-section.no-border {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem 2rem;
  width: 100%;
  box-sizing: border-box;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }
}

.review-info-section {
  margin: 1.5rem 0;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

.review-info-section h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #495057;
}

.review-info-section .status-badge {
  font-size: 0.9rem;
  padding: 0.35rem 0.75rem;
}

.review-info-section .status-badge.submitted {
  background: #fef3c7;
  color: #92400e;
}

.review-info-section .status-badge.approved {
  background: #d1fae5;
  color: #065f46;
}

.review-info-section .status-badge.failed {
  background: #fee2e2;
  color: #991b1b;
}

.full-width {
  grid-column: 1 / -1;
  width: 100%;
}

/* Force consistency in BaseInput within this modal */
.modal-form :deep(.form-group) {
  width: 100%;
  margin-bottom: 0;
}

.modal-form :deep(input),
.modal-form :deep(select),
.modal-form :deep(textarea) {
  width: 100%;
  box-sizing: border-box;
  display: block;
}

.error-box {
  background: #fee2e2;
  color: #dc2626;
  padding: 0.8rem;
  border-radius: 6px;
  margin-top: 1rem;
  font-size: 0.85rem;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination button:not(:disabled):hover {
  background: #f3f4f6;
}

.page-info {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Sort Select */
.sort-select {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.sort-select:hover {
  border-color: #667eea;
}

.sort-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Search input */
.search-input {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #ddd;
  font-size: 0.9rem;
  min-width: 200px;
  transition: all 0.2s;
}

.search-input:hover {
  border-color: #667eea;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Mini sort select */
.sort-select-mini {
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s;
  min-width: 50px;
}

.sort-select-mini:hover {
  border-color: #667eea;
}

.sort-select-mini:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
</style>
